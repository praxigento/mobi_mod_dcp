<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report;

/**
 * Get data for DCP Profile report.
 */
interface ProfileInterface
{
    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Profile\Request $request
     * @return \Praxigento\Dcp\Api\Web\Report\Profile\Response
     *
     * Magento 2 WebAPI requires full names in documentation (aliases are not allowed).
     */
    public function exec($request);
}