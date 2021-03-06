<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Downline;

/**
 * Request to get data for DCP Downline report.
 *
 * (Define getters explicitly to use with Swagger tool)
 * (Define setters explicitly to use with Magento JSON2PHP conversion tool)
 *
 */
class Request
    extends \Praxigento\Core\Api\App\Web\RequestCond
{
    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Downline\Request\Data
     */
    public function getData() {
        return parent::getData();
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Downline\Request\Data $data
     * @return void
     */
    public function setData($data) {
        parent::setData($data);
    }

}