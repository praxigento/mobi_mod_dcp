<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer as DCustomer;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus as DOverBonus;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus\Item as DItem;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\OverrideBonus\A\Query as QBGetItems;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs as HGetCalcs;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu as HIsSchemeEu;

/**
 * Action to build "Override Bonus" section of the DCP's "Check" report.
 */
class OverrideBonus {
    /** @var \Praxigento\Core\Api\Helper\Customer\Currency */
    private $hlpCustCurrency;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu */
    private $hlpIsSchemeEu;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\OverrideBonus\A\Query */
    private $qbGetItems;

    public function __construct(
        \Praxigento\Core\Api\Helper\Customer\Currency $hlpCustCurrency,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        QBGetItems $qbGetItems,
        HGetCalcs $hlpGetCalcs,
        HIsSchemeEu $hlpIsSchemeEu
    ) {
        $this->hlpCustCurrency = $hlpCustCurrency;
        $this->hlpPeriod = $hlpPeriod;
        $this->qbGetItems = $qbGetItems;
        $this->hlpGetCalcs = $hlpGetCalcs;
        $this->hlpIsSchemeEu = $hlpIsSchemeEu;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus|null
     * @throws \Exception
     */
    public function exec($custId, $period) {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $items = [];

        /* perform processing */
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {
            $isSchemeEu = $this->hlpIsSchemeEu->exec($custId);
            if ($isSchemeEu) {
                $calcCompress = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE2_EU] ?? null;
                $calcBonus = $calcs[Cfg::CODE_TYPE_CALC_BONUS_OVERRIDE_EU] ?? null;
            } else {
                $calcCompress = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE2_DEF] ?? null;
                $calcBonus = $calcs[Cfg::CODE_TYPE_CALC_BONUS_OVERRIDE_DEF] ?? null;
            }
            if ($calcCompress && $calcBonus) {
                $items = $this->getItems($calcCompress, $calcBonus, $custId, $dsEnd);
            }
        }

        /* compose result */
        $result = new DOverBonus();
        $result->setItems($items);
        return $result;
    }

    /**
     * Get DB data and compose API data.
     *
     * @param $calcCompress
     * @param $calcBonus
     * @param $custId
     * @param $dsEnd
     * @return array
     * @throws \Exception
     */
    private function getItems($calcCompress, $calcBonus, $custId, $dsEnd) {
        $query = $this->qbGetItems->build();
        $conn = $query->getConnection();
        $bind = [
            QBGetItems::BND_CALC_ID_COMPRESS => $calcCompress,
            QBGetItems::BND_CALC_ID_BONUS => $calcBonus,
            QBGetItems::BND_CUST_ID => $custId
        ];
        $rs = $conn->fetchAll($query, $bind);

        $result = [];
        foreach ($rs as $one) {
            /* get DB data */
            $amountBase = $one[QBGetItems::A_AMOUNT];
            $custIdFrom = $one[QBGetItems::A_CUST_ID];
            $depth = $one[QBGetItems::A_DEPTH];
            $mlmId = $one[QBGetItems::A_MLM_ID];
            $nameFirst = $one[QBGetItems::A_NAME_FIRST];
            $nameLast = $one[QBGetItems::A_NAME_LAST];
            $pv = $one[QBGetItems::A_PV];
            $rankCode = $one[QBGetItems::A_RANK_CODE];

            /* calculated values */
            $ds = $dsEnd;
            $yyyy = substr($ds, 0, 4); // YYYY
            $mm = substr($ds, 4, 2); // MM
            $dd = substr($ds, 6, 2); // DD
            $date = "$yyyy-$mm-$dd";
            $amount = $this->hlpCustCurrency->convertFromBase($amountBase, $custId, true, $date);
            $name = "$nameFirst $nameLast";
            $percent = $amountBase / $pv;
            $percent = round($percent, 2);

            /* compose API data */
            $customer = new DCustomer();
            $customer->setId($custIdFrom);
            $customer->setLevel($depth);
            $customer->setMlmId($mlmId);
            $customer->setName($name);
            $item = new DItem();
            $item->setAmount($amount);
            $item->setAmountBase($amountBase);
            $item->setCustomer($customer);
            $item->setPercent($percent);
            $item->setRank($rankCode);
            $item->setVolume($pv);

            $result[] = $item;
        }
        return $result;
    }

}
