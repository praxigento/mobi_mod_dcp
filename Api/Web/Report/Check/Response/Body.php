<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response;

/**
 * This is Magento API data object, so we need to declare get/set methods explicitly.
 */
class Body
    extends \Praxigento\Core\Data
{
    const A_CUSTOMER = 'customer';
    const A_CURRENCY = 'currency';
    const A_PERIOD = 'period';
    const A_SECTIONS = 'sections';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer|null
     */
    public function getCustomer()
    {
        $result = parent::get(self::A_CUSTOMER);
        return $result;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        $result = parent::get(self::A_CURRENCY);
        return $result;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        $result = parent::get(self::A_PERIOD);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections|null
     */
    public function getSections()
    {
        $result = parent::get(self::A_SECTIONS);
        return $result;
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer $data
     */
    public function setCustomer($data)
    {
        parent::set(self::A_CUSTOMER, $data);
    }

    /**
     * @param string $data
     */
    public function setCurrency($data)
    {
        parent::set(self::A_CURRENCY, $data);
    }

    public function setPeriod(string $data)
    {
        parent::set(self::A_PERIOD, $data);
    }

    /**
     * @param  \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections $data
     */
    public function setSections($data)
    {
        parent::set(self::A_SECTIONS, $data);
    }
}