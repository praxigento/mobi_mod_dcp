<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2020
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignUpBonus;


class Item
    extends \Praxigento\Core\Data
{
    const A_AMOUNT = 'amount';
    const A_AMOUNT_BASE = 'amount_base';
    const A_NOTE = 'note';


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
     * @return string
     */
    public function getNote()
    {
        $result = parent::get(self::A_NOTE);
        return $result;
    }

    /**
     * In customer currency (USD|EUR).
     *
     * @param float $data
     * @return void
     */
    public function setAmount($data)
    {
        parent::set(self::A_AMOUNT, $data);
    }

    /**
     * In base currency (USD).
     *
     * @param float $data
     * @return void
     */
    public function setAmountBase($data)
    {
        parent::set(self::A_AMOUNT_BASE, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setNote($data)
    {
        parent::set(self::A_NOTE, $data);
    }
}
