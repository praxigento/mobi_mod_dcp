<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer as DCustomer;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus as DTeamBonus;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus\Item as DItem;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\TeamBonus\A\Query as QBGetItems;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs as HGetCalcs;

/**
 * Action to build "Team Bonus" section of the DCP's "Check" report.
 */
class TeamBonus
{
    /** @var \Praxigento\BonusHybrid\Repo\Dao\Downline */
    private $daoBonDwn;
    /** @var \Praxigento\Core\Api\Helper\Customer\Currency */
    private $hlpCustCurrency;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\TeamBonus\A\Query */
    private $qbGetItems;

    public function __construct(
        \Praxigento\Core\Api\Helper\Customer\Currency $hlpCustCurrency,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\BonusHybrid\Repo\Dao\Downline $daoBonDwn,
        QBGetItems $qbGetItems,
        HGetCalcs $hlpGetCalcs
    ) {
        $this->hlpCustCurrency = $hlpCustCurrency;
        $this->hlpPeriod = $hlpPeriod;
        $this->daoBonDwn = $daoBonDwn;
        $this->qbGetItems = $qbGetItems;
        $this->hlpGetCalcs = $hlpGetCalcs;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus|null
     * @throws \Exception
     */
    public function exec($custId, $period)
    {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $items = [];
        $pv = 0;
        /** TODO: calc value or remove attr */
        $percent = 0;

        /* perform processing */
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (
            isset($calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1]) &&
            isset($calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_DEF]) &&
            isset($calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_EU])
        ) {
            $calcCompressPhaseI = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1];
            $calcDef = $calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_DEF];
            $calcEu = $calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_EU];

            $pv = $this->getPv($calcCompressPhaseI, $custId);
            $items = $this->getItems($calcCompressPhaseI, $calcDef, $calcEu, $custId);
        }

        /* compose result */
        $result = new DTeamBonus();
        $result->setItems($items);
        $result->setTotalVolume($pv);
        $result->setPercent($percent);

        return $result;
    }

    /**
     * Get DB data and compose API data.
     *
     * @param $calcCompressPhaseI
     * @param $calcDef
     * @param $calcEu
     * @param $custId
     * @return array
     * @throws \Exception
     */
    private function getItems($calcCompressPhaseI, $calcDef, $calcEu, $custId)
    {
        $query = $this->qbGetItems->build();
        $conn = $query->getConnection();
        $bind = [
            QBGetItems::BND_CALC_ID_COMPRESS_PHASE_I => $calcCompressPhaseI,
            QBGetItems::BND_CALC_ID_TEAM_DEF => $calcDef,
            QBGetItems::BND_CALC_ID_TEAM_EU => $calcEu,
            QBGetItems::BND_CUST_ID => $custId
        ];
        $rs = $conn->fetchAll($query, $bind);

        $result = [];
        foreach ($rs as $one) {
            /* get DB data */
            $memberId = $one[QBGetItems::A_CUST_ID];
            $depth = $one[QBGetItems::A_DEPTH];
            $mlmId = $one[QBGetItems::A_MLM_ID];
            $nameFirst = $one[QBGetItems::A_NAME_FIRST];
            $nameLast = $one[QBGetItems::A_NAME_LAST];
            $pv = $one[QBGetItems::A_PV];
            $amountBase = $one[QBGetItems::A_AMOUNT];

            /* calculated values */
            $amount = $this->hlpCustCurrency->convertFromBase($amountBase, $custId);
            $name = "$nameFirst $nameLast";
            $percent = round($amountBase / $pv, 2);

            /* compose API data */
            $customer = new DCustomer();
            $customer->setId($memberId);
            $customer->setMlmId($mlmId);
            $customer->setName($name);
            $customer->setLevel($depth);
            $customer->setLevelCompressed($depth);
            $item = new DItem();
            $item->setCustomer($customer);
            $item->setAmount($amount);
            $item->setAmountBase($amountBase);
            $item->setVolume($pv);
            $item->setPercent($percent);

            $result[] = $item;
        }
        return $result;
    }

    /**
     * Get PV (& RankID ???) for given calculation & customer.
     *
     * @param $calcId
     * @param $custId
     * @return float
     */
    private function getPv($calcId, $custId)
    {
        $byCalcId = EBonDwnl::A_CALC_REF . '=' . (int)$calcId;
        $byCustId = EBonDwnl::A_CUST_REF . '=' . (int)$custId;
        $where = "($byCalcId) AND ($byCustId)";
        $rs = $this->daoBonDwn->get($where);
        $row = reset($rs);
        $pv = $row ? $row->get(EBonDwnl::A_PV) : 0;
        return $pv;
    }
}
