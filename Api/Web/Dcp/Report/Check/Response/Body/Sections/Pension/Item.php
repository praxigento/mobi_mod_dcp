<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\Pension;


class Item
    extends \Praxigento\Core\Data
{
    const A_AMOUNT = 'amount';
    const A_DATE = 'date';
    const A_NOTE = 'note';

    /**
     * @return float
     */
    public function getAmount()
    {
        $result = parent::get(self::A_AMOUNT);
        return $result;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        $result = parent::get(self::A_DATE);
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

    public function setAmount($data)
    {
        parent::set(self::A_AMOUNT, $data);
    }

    public function setDate($data)
    {
        parent::set(self::A_DATE, $data);
    }

    public function setNote($data)
    {
        parent::set(self::A_NOTE, $data);
    }
}