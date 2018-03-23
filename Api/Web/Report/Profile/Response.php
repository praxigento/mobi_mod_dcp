<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Profile;

/**
 * Response to get data for DCP Distributor Profile report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Response
    extends \Praxigento\Core\Api\App\Web\Response
{
    const CODE_NO_DATA = 'NO_DATA';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data
     */
    public function getData()
    {
        $result = parent::get(self::A_DATA);
        return $result;
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data $data
     */
    public function setData($data)
    {
        parent::set(self::A_DATA, $data);
    }

}