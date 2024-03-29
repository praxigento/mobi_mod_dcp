<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Totals as DTotals;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals\A\Query\GetAmountCredit as QBGetAmntCredit;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals\A\Query\GetAmountDebit as QBGetAmntDebit;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals\A\Query\GetSumCredit as QBGetSumCredit;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs as HGetCalcs;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu as HIsSchemeEu;

/**
 * Action to build "Totals" section of the DCP's "Check" report.
 */
class Totals
{
    /** @var \Praxigento\Core\Api\Helper\Customer\Currency */
    private $hlpCustCurrency;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu */
    private $hlpIsSchemeEu;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals\A\Query\GetAmountCredit */
    private $qbGetAmntCredit;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals\A\Query\GetAmountDebit */
    private $qbGetAmntDebit;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals\A\Query\GetSumCredit */
    private $qbGetSumCredit;

    public function __construct(
        \Praxigento\Core\Api\Helper\Customer\Currency $hlpCustCurrency,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        QBGetAmntCredit $qbGetAmntCredit,
        QBGetAmntDebit $qbGetAmntDebit,
        QBGetSumCredit $qbGetSumCredit,
        HGetCalcs $hlpGetCalcs,
        HIsSchemeEu $hlpIsSchemeEu
    ) {
        $this->hlpCustCurrency = $hlpCustCurrency;
        $this->hlpPeriod = $hlpPeriod;
        $this->qbGetAmntCredit = $qbGetAmntCredit;
        $this->qbGetAmntDebit = $qbGetAmntDebit;
        $this->qbGetSumCredit = $qbGetSumCredit;
        $this->hlpGetCalcs = $hlpGetCalcs;
        $this->hlpIsSchemeEu = $hlpIsSchemeEu;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Totals|null
     * @throws \Exception
     */
    public function exec($custId, $period)
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
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {
            $isSchemeEu = $this->hlpIsSchemeEu->exec($custId);
            $idBonPers = $calcs[Cfg::CODE_TYPE_CALC_BONUS_PERSONAL] ?? null;
            $idBonCourt = $calcs[Cfg::CODE_TYPE_CALC_BONUS_COURTESY] ?? null;
            $idProcFee = $calcs[Cfg::CODE_TYPE_CALC_PROC_FEE] ?? null;
            if ($isSchemeEu) {
                $idBonTeam = $calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_EU] ?? null;
                $idBonOver = $calcs[Cfg::CODE_TYPE_CALC_BONUS_OVERRIDE_EU] ?? null;
                $idBonInf = $calcs[Cfg::CODE_TYPE_CALC_BONUS_INFINITY_EU] ?? null;
            } else {
                $idBonTeam = $calcs[Cfg::CODE_TYPE_CALC_BONUS_TEAM_DEF] ?? null;
                $idBonOver = $calcs[Cfg::CODE_TYPE_CALC_BONUS_OVERRIDE_DEF] ?? null;
                $idBonInf = $calcs[Cfg::CODE_TYPE_CALC_BONUS_INFINITY_DEF] ?? null;
            }

            /* fetch data from DB */
            $amntPers = $this->getAmountCredit($idBonPers, $custId);
            $amntTeam = $this->getSumCredit($idBonTeam, $custId);
            $amntCourt = $this->getSumCredit($idBonCourt, $custId);
            $amntOver = $this->getSumCredit($idBonOver, $custId);
            $amntInf = $this->getSumCredit($idBonInf, $custId);
            $amntFee = $this->getAmountDebit($idProcFee, $custId);

            // get date as string to convert EUR
            $ds = $dsEnd;
            $yyyy = substr($ds, 0, 4); // YYYY
            $mm = substr($ds, 4, 2); // MM
            $dd = substr($ds, 6, 2); // DD
            $date = "$yyyy-$mm-$dd";
            /* convert values to customer currency */
            $amntPers = $this->hlpCustCurrency->convertFromBase($amntPers, $custId, true, $date);
            $amntTeam = $this->hlpCustCurrency->convertFromBase($amntTeam, $custId, true, $date);
            $amntCourt = $this->hlpCustCurrency->convertFromBase($amntCourt, $custId, true, $date);
            $amntOver = $this->hlpCustCurrency->convertFromBase($amntOver, $custId, true, $date);
            $amntInf = $this->hlpCustCurrency->convertFromBase($amntInf, $custId, true, $date);
            $amntFee = $this->hlpCustCurrency->convertFromBase($amntFee, $custId, true, $date);

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
