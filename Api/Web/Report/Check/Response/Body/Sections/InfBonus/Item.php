<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus;


class Item
    extends \Praxigento\Core\Data
{

    const A_AMOUNT = 'amount';
    const A_AMOUNT_BASE = 'amount_base';
    const A_CUSTOMER = 'customer';
    const A_PERCENT = 'percent';
    const A_RANK = 'rank';
    const A_VOLUME = 'volume';

    /**
     * In customer currency (USD|EUR).
     *
     * @return float
     */
    public function getAmount()
    {
        $result = parent::get(self::A_AMOUNT);
        return $result;
    }

    /**
     * In base currency (USD).
     *
     * @return float
     */
    public function getAmountBase()
    {
        $result = parent::get(self::A_AMOUNT_BASE);
        return $result;
    }

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
    public function getPercent()
    {
        $result = parent::get(self::A_PERCENT);
        return $result;
    }

    /**
     * @return string
     */
    public function getRank()
    {
        $result = parent::get(self::A_RANK);
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
     * In customer currency (USD|EUR).
     *
     * @param $data
     * @return void
     */
    public function setAmount($data)
    {
        parent::set(self::A_AMOUNT, $data);
    }

    /**
     * In base currency (USD).
     *
     * @param $data
     * @return void
     */
    public function setAmountBase($data)
    {
        parent::set(self::A_AMOUNT_BASE, $data);
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
    public function setPercent($data)
    {
        parent::set(self::A_PERCENT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setRank($data)
    {
        parent::set(self::A_RANK, $data);
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
