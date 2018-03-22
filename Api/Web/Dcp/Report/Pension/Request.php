<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report\Pension;

/**
 * Request to get data for DCP Pension report.
 *
 * (Define getters explicitly to use with Swagger tool)
 * (Define setters explicitly to use with Magento JSON2PHP conversion tool)
 *
 */
class Request
    extends \Praxigento\Core\Api\App\Web\Request
{
    /**
     * @return \Praxigento\Core\Data
     */
    public function getData()
    {
        return parent::getData();
    }

    /**
     * @param \Praxigento\Core\Data $data
     */
    public function setData($data)
    {
        parent::setData($data);
    }

}