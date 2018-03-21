<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Customer as DCustomer;
use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\InfBonus as DInfBonus;
use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\InfBonus\Item as DItem;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OverrideBonus\A\Query as QBGetItems;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Z\Helper\GetCalcs as RouGetCalcs;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu as RouIsSchemeEu;
use Praxigento\Santegra\Config as Cfg;

/**
 * Action to build "Infinity Bonus" section of the DCP's "Check" report.
 */
class InfinityBonus
{
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\InfinityBonus\A\Query */
    private $qbGetItems;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $rouGetCalcs;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu */
    private $rouIsSchemeEu;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        QBGetItems $qbGetItems,
        RouGetCalcs $rouGetCalcs,
        RouIsSchemeEu $rouIsSchemeEu
    )
    {
        $this->hlpPeriod = $hlpPeriod;
        $this->qbGetItems = $qbGetItems;
        $this->rouGetCalcs = $rouGetCalcs;
        $this->rouIsSchemeEu = $rouIsSchemeEu;
    }

    public function exec($custId, $period): DInfBonus
    {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $items = [];

        /* perform processing */
        $calcs = $this->rouGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {
            $isSchemeEu = $this->rouIsSchemeEu->exec($custId);
            if ($isSchemeEu) {
                $calcCompress = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE2_EU];
                $calcBonus = $calcs[Cfg::CODE_TYPE_CALC_BONUS_INFINITY_EU];
            } else {
                $calcCompress = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE2_DEF];
                $calcBonus = $calcs[Cfg::CODE_TYPE_CALC_BONUS_INFINITY_DEF];
            }

            $items = $this->getItems($calcCompress, $calcBonus, $custId);
        }

        /* compose result */
        $result = new DInfBonus();
        $result->setItems($items);
        return $result;
    }

    /**
     * Get DB data and compose API data.
     *
     * @param $calcCompress
     * @param $calcBonus
     * @param $custId
     * @return array
     */
    private function getItems($calcCompress, $calcBonus, $custId)
    {
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
            $amount = $one[QBGetItems::A_AMOUNT];
            $custId = $one[QBGetItems::A_CUST_ID];
            $depth = $one[QBGetItems::A_DEPTH];
            $mlmId = $one[QBGetItems::A_MLM_ID];
            $nameFirst = $one[QBGetItems::A_NAME_FIRST];
            $nameLast = $one[QBGetItems::A_NAME_LAST];
            $pv = $one[QBGetItems::A_PV];
            $rankCode = $one[QBGetItems::A_RANK_CODE];

            /* composite values */
            $name = "$nameFirst $nameLast";
            $percent = $amount / $pv;
            $percent = round($percent, 2);

            /* compose API data */
            $customer = new DCustomer();
            $customer->setId($custId);
            $customer->setLevel($depth);
            $customer->setMlmId($mlmId);
            $customer->setName($name);
            $item = new DItem();
            $item->setAmount($amount);
            $item->setCustomer($customer);
            $item->setPercent($percent);
            $item->setRank($rankCode);
            $item->setVolume($pv);

            $result[] = $item;
        }
        return $result;
    }

}