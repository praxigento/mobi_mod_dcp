<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Downline;

/**
 * Response to get data for DCP Downline report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Response
    extends \Praxigento\Core\Api\App\Web\Response
{
    const CODE_NO_DATA = 'NO_DATA';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Downline\Response\Entry[]
     */
    public function getData()
    {
        $result = parent::get(self::A_DATA);
        return $result;
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Downline\Response\Entry[] $data
     * @return void
     */
    public function setData($data)
    {
        parent::set(self::A_DATA, $data);
    }

}