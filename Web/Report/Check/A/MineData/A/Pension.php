<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Pension as DPension;

/**
 * Action to build "Pension" section of the DCP's "Check" report.
 */
class Pension
{
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\PensionFund\Repo\Entity\Registry */
    private $repoReg;

    public function __construct(
        \Praxigento\PensionFund\Repo\Entity\Registry $repoReg,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs $hlpGetCalcs
    ) {
        $this->repoReg = $repoReg;
        $this->hlpPeriod = $hlpPeriod;
        $this->hlpGetCalcs = $hlpGetCalcs;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Pension|null
     * @throws \Exception
     */
    public function exec($custId, $period)
    {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */


        /* perform processing */
        $pension = $this->repoReg->getById($custId);
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {

        }

        /* compose result */
        $result = new DPension();

        return $result;
    }

}