<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer as DCustomer;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Customer\A\Query as QBGetCustomer;

/**
 * Utility to build "Customer" property of the DCP's "Check" report.
 */
class Customer
{
    /** @var \Praxigento\BonusHybrid\Repo\Dao\Downline */
    private $daoBonDwn;
    /** @var \Praxigento\BonusBase\Repo\Dao\Rank */
    private $daoRank;
    /** @var \Praxigento\Dcp\Api\Helper\Map */
    private $hlpDcpMap;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Customer\A\Query */
    private $qbGetCustomer;

    public function __construct(
        \Praxigento\BonusBase\Repo\Dao\Rank $daoRank,
        \Praxigento\BonusHybrid\Repo\Dao\Downline $daoBonDwn,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Api\Helper\Map $hlpDcpMap,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs $hlpGetCalcs,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Customer\A\Query $qbGetCustomer
    ) {
        $this->daoRank = $daoRank;
        $this->daoBonDwn = $daoBonDwn;
        $this->hlpPeriod = $hlpPeriod;
        $this->hlpDcpMap = $hlpDcpMap;
        $this->hlpGetCalcs = $hlpGetCalcs;
        $this->qbGetCustomer = $qbGetCustomer;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer|null
     * @throws \Exception
     */
    public function exec($custId, $period)
    {
        /* define local working data */
        $onDate = $this->hlpPeriod->getPeriodLastDate($period);

        /* prepare query & parameters */
        $query = $this->qbGetCustomer->build();
        $bind = [
            QBGetCustomer::BND_ON_DATE => $onDate,
            QBGetCustomer::BND_CUST_ID => $custId
        ];

        /* perform query and extract data from result set */
        $conn = $query->getConnection();
        $rs = $conn->fetchRow($query, $bind);

        $custId = $rs[QBGetCustomer::A_CUST_ID] ?? null;
        $mlmId = $rs[QBGetCustomer::A_MLM_ID] ?? null;
        $level = $rs[QBGetCustomer::A_DEPTH] ?? null;
        $nameFirst = $rs[QBGetCustomer::A_NAME_FIRST] ?? null;
        $nameLast = $rs[QBGetCustomer::A_NAME_LAST] ?? null;
        $name = "$nameFirst $nameLast";
        $rank = $this->getRank($period, $custId);

        /* compose result */
        $result = null;
        if ($custId) {
            $result = new DCustomer();
            $result->setId($custId);
            $result->setMlmId($mlmId);
            $result->setName($name);
            $result->setLevel($level);
            $result->setRank($rank);
        }
        return $result;
    }

    private function getRank($period, $custId)
    {
        $rankCode = Cfg::RANK_UNRANKED;

        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (isset($calcs[Cfg::CODE_TYPE_CALC_PV_WRITE_OFF])) {
            $calcId = $calcs[Cfg::CODE_TYPE_CALC_PV_WRITE_OFF];
            $rankCode = $this->getRankCodeForCalc($calcId, $custId);
        } elseif (isset($calcs[Cfg::CODE_TYPE_CALC_FORECAST_PHASE1])) {
            $calcId = $calcs[Cfg::CODE_TYPE_CALC_FORECAST_PHASE1];
            $rankCode = $this->getRankCodeForCalc($calcId, $custId);
        }
        $result = $this->hlpDcpMap->rankCodeToUi($rankCode);
        return $result;
    }

    /**
     * Get rank code for given calculation & customer.
     *
     * @param int $calcId
     * @param int $custId
     */
    private function getRankCodeForCalc($calcId, $custId)
    {
        $result = Cfg::RANK_UNRANKED;

        $byCalcId = EBonDwnl::A_CALC_REF . '=' . (int)$calcId;
        $byCustId = EBonDwnl::A_CUST_REF . '=' . (int)$custId;
        $where = "($byCalcId) AND ($byCustId)";
        $rs = $this->daoBonDwn->get($where);
        if (
            is_array($rs) &&
            (count($rs) > 0)
        ) {
            $row = reset($rs);
            $rankId = $row->get(EBonDwnl::A_RANK_REF);
            $rank = $this->daoRank->getById($rankId);
            $result = $rank->getCode();
        }
        return $result;
    }
}