<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2020
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections;


class SignupBonus
    extends \Praxigento\Core\Data
{
    const A_ITEMS = 'items';
    const A_TOTAL_AMOUNT = 'total_amount';
    const A_TOTAL_AMOUNT_BASE = 'total_amount_base';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignUpBonus\Item[]
     */
    public function getItems()
    {
        $result = parent::get(self::A_ITEMS);
        return $result;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        $result = parent::get(self::A_TOTAL_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getTotalAmountBase(): float
    {
        $result = parent::get(self::A_TOTAL_AMOUNT_BASE);
        return $result;
    }

    /**
     * @param $data
     * @return void
     */
    public function setItems($data)
    {
        parent::set(self::A_ITEMS, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setTotalAmount($data)
    {
        parent::set(self::A_TOTAL_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setTotalAmountBase($data)
    {
        parent::set(self::A_TOTAL_AMOUNT_BASE, $data);
    }
}
