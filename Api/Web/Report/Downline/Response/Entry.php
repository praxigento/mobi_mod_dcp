<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Downline\Response;

/**
 * Accessors use 'CamelCase' naming strategy (data object default), but data inside use 'snake_case' naming strategy
 * (API default). Repo queries should use 'snake_case' namings to prepare array data, DataObject will return
 * 'snake_case' property if 'CamelCase' will not be found.
 */
class Entry
    extends \Praxigento\Core\Data
{
    const A_COUNTRY = 'country';
    const A_CUSTOMER_REF = 'customer_ref';
    const A_DEPTH = 'depth';
    const A_EMAIL = 'email';
    const A_MLM_ID = 'mlm_id';
    const A_NAME_FIRST = 'name_first';
    const A_NAME_LAST = 'name_last';
    const A_OV = 'ov';
    const A_PARENT_REF = 'parent_ref';
    const A_PATH = 'path';
    const A_PV = 'pv';
    const A_RANK_CODE = 'rank_code';
    const A_TV = 'tv';
    const A_UNQ_MONTHS = 'unq_months';

    /**
     * @return string
     */
    public function getCountry()
    {
        $result = parent::get(self::A_COUNTRY);
        return $result;
    }

    /**
     * @return int
     */
    public function getCustomerRef()
    {
        $result = parent::get(self::A_CUSTOMER_REF);
        return $result;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        $result = parent::get(self::A_DEPTH);
        return $result;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        $result = parent::get(self::A_EMAIL);
        return $result;
    }

    /**
     * @return string
     */
    public function getMlmId()
    {
        $result = parent::get(self::A_MLM_ID);
        return $result;
    }

    /**
     * @return string
     */
    public function getNameFirst()
    {
        $result = parent::get(self::A_NAME_FIRST);
        return $result;
    }

    /**
     * @return string
     */
    public function getNameLast()
    {
        $result = parent::get(self::A_NAME_LAST);
        return $result;
    }

    /**
     * @return float
     */
    public function getOv()
    {
        $result = parent::get(self::A_OV);
        return $result;
    }

    /**
     * @return int
     */
    public function getParentRef()
    {
        $result = parent::get(self::A_PARENT_REF);
        return $result;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $result = parent::get(self::A_PATH);
        return $result;
    }

    /**
     * @return float
     */
    public function getPv()
    {
        $result = parent::get(self::A_PV);
        return $result;
    }

    /**
     * @return string
     */
    public function getRankCode()
    {
        $result = parent::get(self::A_RANK_CODE);
        return $result;
    }

    /**
     * @return float
     */
    public function getTv()
    {
        $result = parent::get(self::A_TV);
        return $result;
    }

    /**
     * @return int
     */
    public function getUnqMonths()
    {
        $result = parent::get(self::A_UNQ_MONTHS);
        return $result;
    }

    /**
     * @param string $data
     * @return void
     */
    public function setCountry($data)
    {
        parent::set(self::A_COUNTRY, $data);
    }

    /**
     * @param int $data
     * @return void
     */
    public function setCustomerRef($data)
    {
        parent::set(self::A_CUSTOMER_REF, $data);
    }

    /**
     * @param int $data
     * @return void
     */
    public function setDepth($data)
    {
        parent::set(self::A_DEPTH, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setEmail($data)
    {
        parent::set(self::A_EMAIL, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setMlmId($data)
    {
        parent::set(self::A_MLM_ID, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setNameFirst($data)
    {
        parent::set(self::A_NAME_FIRST, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setNameLast($data)
    {
        parent::set(self::A_NAME_LAST, $data);
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
     * @param int $data
     * @return void
     */
    public function setParentRef($data)
    {
        parent::set(self::A_PARENT_REF, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setPath($data)
    {
        parent::set(self::A_PATH, $data);
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
    public function setRankCode($data)
    {
        parent::set(self::A_RANK_CODE, $data);
    }

    /**
     * @param float $data
     * @return void
     */
    public function setTv($data)
    {
        parent::set(self::A_TV, $data);
    }

    /**
     * @param int $data
     * @return void
     */
    public function setUnqMonths($data)
    {
        parent::set(self::A_UNQ_MONTHS, $data);
    }
}