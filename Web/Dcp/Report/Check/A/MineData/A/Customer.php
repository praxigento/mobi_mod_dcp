<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Customer as DCustomer;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer\A\Query as QBGetCustomer;

/**
 * Utility to build "Customer" property of the DCP's "Check" report.
 */
class Customer
{
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer\A\Query */
    private $qbGetCustomer;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer\A\Query $qbGetCustomer
    )
    {
        $this->hlpPeriod = $hlpPeriod;
        $this->qbGetCustomer = $qbGetCustomer;
    }

    public function exec($custId, $period): DCustomer
    {
        $result = new DCustomer();

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

        /* compose result */
        $result->setId($custId);
        $result->setMlmId($mlmId);
        $result->setLevel($level);
        $result->setName($name);

        return $result;
    }
}