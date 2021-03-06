<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Profile\Response\Data;

/**
 * Bonus stats data for DCP Distributor Profile report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class BonusStats
    extends \Praxigento\Core\Data
{
    const A_DATE_UPDATED = 'date_updated';
    const A_OV = 'ov';
    const A_PV = 'pv';
    const A_RANK = 'rank';
    const A_TV = 'tv';

    /** @return string */
    public function getDateUpdated()
    {
        $result = parent::get(self::A_DATE_UPDATED);
        return $result;
    }

    /** @return float */
    public function getOv()
    {
        $result = parent::get(self::A_OV);
        return $result;
    }

    /** @return float */
    public function getPv()
    {
        $result = parent::get(self::A_PV);
        return $result;
    }

    /** @return string */
    public function getRank()
    {
        $result = parent::get(self::A_RANK);
        return $result;
    }

    /** @return float */
    public function getTv()
    {
        $result = parent::get(self::A_TV);
        return $result;
    }

    /**
     * @param string $data
     * @return void
     */
    public function setDateUpdated($data)
    {
        parent::set(self::A_DATE_UPDATED, $data);
    }

    /**
     * @param float $data
     * @return void
     */
    public function setOv($data)
    {
        parent::set(self::A_OV, $data);
    }

    /**
     * @param float $data
     * @return void
     */
    public function setPv($data)
    {
        parent::set(self::A_PV, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setRank($data)
    {
        parent::set(self::A_RANK, $data);
    }

    /**
     * @param float $data
     * @return void
     */
    public function setTv($data)
    {
        parent::set(self::A_TV, $data);
    }

}