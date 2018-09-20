<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Profile\Response\Data;

/**
 * Pensions data for DCP Distributor Profile report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Pension
    extends \Praxigento\Core\Data
{
    const A_MONTH_LEFT = 'month_left';
    const A_MONTH_SINCE = 'month_since';
    const A_MONTH_TOTAL = 'month_total';
    const A_MONTH_UNQ = 'month_unq';

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

    /**
     * @param int $data
     * @return void
     */
    public function setMonthLeft($data)
    {
        parent::set(self::A_MONTH_LEFT, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setMonthSince($data)
    {
        parent::set(self::A_MONTH_SINCE, $data);
    }

    /**
     * @param int $data
     * @return void
     */
    public function setMonthTotal($data)
    {
        parent::set(self::A_MONTH_TOTAL, $data);
    }

    /**
     * @param int $data
     * @return void
     */
    public function setMonthUnq($data)
    {
        parent::set(self::A_MONTH_UNQ, $data);
    }
}