<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections;


class Pension
    extends \Praxigento\Core\Data
{
    const A_BALANCE_CLOSE = 'balance_close';
    const A_BALANCE_OPEN = 'balance_open';
    const A_ITEMS = 'items';
    const A_MONTH_LEFT = 'month_left';
    const A_MONTH_SINCE = 'month_since';
    const A_MONTH_TOTAL = 'month_total';
    const A_MONTH_UNQ = 'month_unq';

    /**
     * @return float
     */
    public function getBalanceClose()
    {
        $result = parent::get(self::A_BALANCE_CLOSE);
        return $result;
    }

    /**
     * @return float
     */
    public function getBalanceOpen()
    {
        $result = parent::get(self::A_BALANCE_OPEN);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\Pension\Item[]
     */
    public function getItems()
    {
        $result = parent::get(self::A_ITEMS);
        return $result;
    }

    /**
     * @return int
     */
    public function getMonthLeft()
    {
        $result = parent::get(self::A_MONTH_LEFT);
        return $result;
    }

    /**
     * @return string YYYY/MM
     */
    public function getMonthSince()
    {
        $result = parent::get(self::A_MONTH_SINCE);
        return $result;
    }

    /**
     * @return int
     */
    public function getMonthTotal()
    {
        $result = parent::get(self::A_MONTH_TOTAL);
        return $result;
    }

    /**
     * @return int
     */
    public function getMonthUnq()
    {
        $result = parent::get(self::A_MONTH_UNQ);
        return $result;
    }


    public function setBalanceClose($data)
    {
        parent::set(self::A_BALANCE_CLOSE, $data);
    }

    public function setBalanceOpen($data)
    {
        parent::set(self::A_BALANCE_OPEN, $data);
    }

    public function setItems($data)
    {
        parent::set(self::A_ITEMS, $data);
    }

    public function setMonthLeft($data)
    {
        parent::set(self::A_MONTH_LEFT, $data);
    }

    public function setMonthSince($data)
    {
        parent::set(self::A_MONTH_SINCE, $data);
    }

    public function setMonthTotal($data)
    {
        parent::set(self::A_MONTH_TOTAL, $data);
    }

    public function setMonthUnq($data)
    {
        parent::set(self::A_MONTH_UNQ, $data);
    }


}