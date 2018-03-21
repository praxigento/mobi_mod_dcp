<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\Totals as DTotals;
use Praxigento\Santegra\Config as Cfg;
use Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\A\Fun\Rou\GetCalcs as RouGetCalcs;
use Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\A\Fun\Rou\IsSchemeEu as RouIsSchemeEu;
use Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\Totals\Db\Query\GetAmountCredit as QBGetAmntCredit;
use Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\Totals\Db\Query\GetAmountDebit as QBGetAmntDebit;
use Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\Totals\Db\Query\GetSumCredit as QBGetSumCredit;

/**
 * Action to build "Totals" section of the DCP's "Check" report.
 */
class Totals
{
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\Totals\Db\Query\GetAmountCredit */
    private $qbGetAmntCredit;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\Totals\Db\Query\GetAmountDebit */
    private $qbGetAmntDebit;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\Totals\Db\Query\GetSumCredit */
    private $qbGetSumCredit;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\A\Fun\Rou\GetCalcs */
    private $rouGetCalcs;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc\MineData\A\Fun\Rou\IsSchemeEu */
    private $rouIsSchemeEu;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        QBGetAmntCredit $qbGetAmntCredit,
        QBGetAmntDebit $qbGetAmntDebit,
        QBGetSumCredit $qbGetSumCredit,
        RouGetCalcs $rouGetCalcs,
        RouIsSchemeEu $rouIsSchemeEu
    )
    {
        $this->hlpPeriod = $hlpPeriod;
        $this->qbGetAmntCredit = $qbGetAmntCredit;
        $this->qbGetAmntDebit = $qbGetAmntDebit;
        $this->qbGetSumCredit = $qbGetSumCredit;
        $this->rouGetCalcs = $rouGetCalcs;
        $this->rouIsSchemeEu = $rouIsSchemeEu;
    }

    public function exec($custId, $period): DTotals
    {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $amntCourt = 0;
        $amntInf = 0;
        $amntNet = 0;
        $amntOver = 0;
        $amntPers = 0;
        $amntTeam = 0;
        $amntTotal = 0;
        $amntFee = 0;

        /* perform processing */
        $calcs = $this->rouGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {
            $isSchemeEu = $this->rouIsSchemeEu->exec($custId);
            $idBonPers = $calcs[Cfg::CODE_TYPE_CALC_BONUS_PERSONAL];
            $idBonCourt = $calcs[Cfg::CODE_TYPE_CALC_BONUS_COURTESY];
            $idProcFee = $calcs[Cfg::CODE_TYPE_CALC_PROC_FEE];
            if ($isSchemeEu) {
                $idBonTeam = $calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_EU];
                $idBonOver = $calcs[Cfg::CODE_TYPE_CALC_BONUS_OVERRIDE_EU];
                $idBonInf = $calcs[Cfg::CODE_TYPE_CALC_BONUS_INFINITY_EU];
            } else {
                $idBonTeam = $calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_DEF];
                $idBonOver = $calcs[Cfg::CODE_TYPE_CALC_BONUS_OVERRIDE_DEF];
                $idBonInf = $calcs[Cfg::CODE_TYPE_CALC_BONUS_INFINITY_DEF];
            }

            /* fetch data from DB */
            $amntPers = $this->getAmountCredit($idBonPers, $custId);
            $amntTeam = $this->getSumCredit($idBonTeam, $custId);
            $amntCourt = $this->getAmountCredit($idBonCourt, $custId);
            $amntOver = $this->getSumCredit($idBonOver, $custId);
            $amntInf = $this->getSumCredit($idBonInf, $custId);
            $amntFee = $this->getAmountDebit($idProcFee, $custId);
            $amntTotal = $amntPers + $amntTeam + $amntCourt + $amntOver + $amntInf;
            $amntNet = $amntTotal - $amntFee;
        }

        /* compose result */
        $result = new DTotals();
        $result->setPersonalAmount($amntPers);
        $result->setTeamAmount($amntTeam);
        $result->setCourtesyAmount($amntCourt);
        $result->setOverrideAmount($amntOver);
        $result->setInfinityAmount($amntInf);
        $result->setTotalAmount($amntTotal);
        $result->setProcessingFee($amntFee);
        $result->setNetAmount($amntNet);
        return $result;
    }

    private function getAmountCredit($calcId, $custId)
    {
        $query = $this->qbGetAmntCredit->build();
        $bind = [
            QBGetAmntCredit::BND_CALC_ID => $calcId,
            QBGetAmntCredit::BND_CUST_ID => $custId
        ];
        $conn = $query->getConnection();
        $result = $conn->fetchOne($query, $bind);
        $result = $result ? $result : 0;
        return $result;
    }

    private function getAmountDebit($calcId, $custId)
    {
        $query = $this->qbGetAmntDebit->build();
        $bind = [
            QBGetAmntDebit::BND_CALC_ID => $calcId,
            QBGetAmntDebit::BND_CUST_ID => $custId
        ];
        $conn = $query->getConnection();
        $result = $conn->fetchOne($query, $bind);
        $result = $result ? $result : 0;
        return $result;
    }

    private function getSumCredit($calcId, $custId)
    {
        $query = $this->qbGetSumCredit->build();
        $bind = [
            QBGetSumCredit::BND_CALC_ID => $calcId,
            QBGetSumCredit::BND_CUST_ID => $custId
        ];
        $conn = $query->getConnection();
        $result = $conn->fetchOne($query, $bind);
        $result = $result ? $result : 0;
        return $result;
    }

}