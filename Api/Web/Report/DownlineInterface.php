<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report;

/**
 * Get data for DCP Downline report.
 */
interface DownlineInterface
{
    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Downline\Request $request
     * @return \Praxigento\Dcp\Api\Web\Report\Downline\Response
     *
     * Magento 2 WebAPI requires full names in documentation (aliases are not allowed).
     */
    public function exec($request);
}