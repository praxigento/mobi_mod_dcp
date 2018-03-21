<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\Pension as DPension;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Z\Helper\GetCalcs as HGetCalcs;

/**
 * Action to build "Pension" section of the DCP's "Check" report.
 */
class Pension
{
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        HGetCalcs $hlpGetCalcs
    ) {
        $this->hlpPeriod = $hlpPeriod;
        $this->hlpGetCalcs = $hlpGetCalcs;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\Pension|null
     * @throws \Exception
     */
    public function exec($custId, $period)
    {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */


        /* perform processing */
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {

        }

        /* compose result */
        $result = new DPension();

        return $result;
    }

}