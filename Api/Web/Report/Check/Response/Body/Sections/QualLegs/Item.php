<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs;


class Item
    extends \Praxigento\Core\Data
{
    const A_CUSTOMER = 'customer';
    const A_VOLUME = 'volume';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer
     */
    public function getCustomer()
    {
        $result = parent::get(self::A_CUSTOMER);
        return $result;
    }

    /**
     * @return float
     */
    public function getVolume()
    {
        $result = parent::get(self::A_VOLUME);
        return $result;
    }

    /**
     * @param $data
     * @return void
     */
    public function setCustomer($data)
    {
        parent::set(self::A_CUSTOMER, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setVolume($data)
    {
        parent::set(self::A_VOLUME, $data);
    }

}