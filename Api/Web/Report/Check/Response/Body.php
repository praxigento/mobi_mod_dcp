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
    const A_CURRENCY = 'currency';
    const A_CURRENCY_BASE = 'currency_base';
    const A_CUSTOMER = 'customer';
    const A_PERIOD = 'period';
    const A_SECTIONS = 'sections';

    /**
     * Customer currency (EUR|USD).
     *
     * @return string
     */
    public function getCurrency()
    {
        $result = parent::get(self::A_CURRENCY);
        return $result;
    }

    /**
     * Base currency (USD).
     *
     * @return string
     */
    public function getCurrencyBase()
    {
        $result = parent::get(self::A_CURRENCY_BASE);
        return $result;
    }

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
     * Customer currency (EUR|USD).
     *
     * @param string $data
     * @return void
     */
    public function setCurrency($data)
    {
        parent::set(self::A_CURRENCY, $data);
    }

    /**
     * Base currency (USD).
     *
     * @param string $data
     * @return void
     */
    public function setCurrencyBase($data)
    {
        parent::set(self::A_CURRENCY_BASE, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer $data
     * @return void
     */
    public function setCustomer($data)
    {
        parent::set(self::A_CUSTOMER, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setPeriod($data)
    {
        parent::set(self::A_PERIOD, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections $data
     * @return void
     */
    public function setSections($data)
    {
        parent::set(self::A_SECTIONS, $data);
    }
}
