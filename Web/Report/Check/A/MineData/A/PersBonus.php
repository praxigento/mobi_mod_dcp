<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\PersonalBonus as DPersonalBonus;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs as HGetCalcs;

/**
 * Action to build "Personal Bonus" section of the DCP's "Check" report.
 */
class PersBonus
{
    /** @var \Praxigento\BonusHybrid\Repo\Dao\Downline */
    private $daoBonDwn;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\BonusHybrid\Repo\Dao\Downline $daoBonDwn,
        HGetCalcs $hlpGetCalcs
    ) {
        $this->hlpPeriod = $hlpPeriod;
        $this->daoBonDwn = $daoBonDwn;
        $this->hlpGetCalcs = $hlpGetCalcs;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\PersonalBonus|null
     * @throws \Exception
     */
    public function exec($custId, $period)
    {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $pvCompress = 0;
        $pvOwn = 0;
        /** TODO: calc value or remove attr */
        $percent = 0;

        /* perform processing */
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (
            isset($calcs[Cfg::CODE_TYPE_CALC_PV_WRITE_OFF]) &&
            isset($calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1])
        ) {
            $calcPvWriteOff = $calcs[Cfg::CODE_TYPE_CALC_PV_WRITE_OFF];
            $calcCompress = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1];

            $pvOwn = $this->getPv($calcPvWriteOff, $custId);
            $pvCompress = $this->getPv($calcCompress, $custId);
        }

        /* compose result */
        $result = new DPersonalBonus();
        $result->setCompressedVolume($pvCompress);
        $result->setOwnVolume($pvOwn);
        $result->setPercent($percent);
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
        $result = 0;
        $byCalcId = EBonDwnl::A_CALC_REF . '=' . (int)$calcId;
        $byCustId = EBonDwnl::A_CUST_REF . '=' . (int)$custId;
        $where = "($byCalcId) AND ($byCustId)";
        $rs = $this->daoBonDwn->get($where);
        if ($rs) {
            $row = reset($rs);
            $result = $row->get(EBonDwnl::A_PV);
        }
        return $result;
    }
}