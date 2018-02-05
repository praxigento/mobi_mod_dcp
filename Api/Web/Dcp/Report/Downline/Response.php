<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report\Downline;

/**
 * Response to get data for DCP Downline report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Response
    extends \Praxigento\Core\App\Api\Web\Response
{
    /**
     * @return \Praxigento\Dcp\Api\Web\Dcp\Report\Downline\Response\Entry[]
     */
    public function getData()
    {
        $result = parent::get(self::ATTR_DATA);
        return $result;
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Dcp\Report\Downline\Response\Entry[] $data
     */
    public function setData($data)
    {
        parent::set(self::ATTR_DATA, $data);
    }

}