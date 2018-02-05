<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report;

use Praxigento\Dcp\Api\Web\Dcp\Report\Accounting\Request as ARequest;
use Praxigento\Dcp\Api\Web\Dcp\Report\Accounting\Response as AResponse;

/**
 * Get data for DCP Accounting report.
 */
interface AccountingInterface
{
    /**
     * @param \Praxigento\Dcp\Api\Web\Dcp\Report\Accounting\Request $request
     * @return \Praxigento\Dcp\Api\Web\Dcp\Report\Accounting\Response
     *
     * Magento 2 WebAPI requires full names in documentation (aliases are not allowed).
     */
    public function exec(ARequest $request): AResponse;
}