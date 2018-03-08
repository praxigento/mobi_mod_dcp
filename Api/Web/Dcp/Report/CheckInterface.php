<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report;

/**
 * Get data for DCP Check report.
 */
interface CheckInterface
{
    /**
     * @param \Praxigento\Dcp\Api\Web\Dcp\Report\Check\Request $request
     * @return \Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response
     *
     * Magento 2 WebAPI requires full names in documentation (aliases are not allowed).
     */
    public function exec($request);
}