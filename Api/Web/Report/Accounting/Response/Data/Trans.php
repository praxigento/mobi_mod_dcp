<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data;

/**
 * Accessors use 'CamelCase' naming strategy (data object default), but data inside use 'snake_case' naming strategy
 * (API default). Repo queries should use 'snake_case' namings to prepare array data, DataObject will return
 * 'snake_case' property if 'CamelCase' will not be found.
 */
class Trans
    extends \Praxigento\Core\Data
{
    const A_ASSET = 'asset';
    const A_CUSTOMER_ID = 'customer_id';
    const A_CUSTOMER_NAME = 'customer_name';
    const A_DATE = 'date';
    const A_DETAILS = 'details';
    const A_TRANS_ID = 'trans_id';
    const A_TYPE = 'type';
    const A_VALUE = 'value';

    /**
     * @return string
     */
    public function getAsset()
    {
        $result = parent::get(self::A_ASSET);
        return $result;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        $result = parent::get(self::A_CUSTOMER_ID);
        return $result;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $result = parent::get(self::A_CUSTOMER_NAME);
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
    public function getDetails()
    {
        $result = parent::get(self::A_DETAILS);
        return $result;
    }

    /**
     * @return int
     */
    public function getTransId()
    {
        $result = parent::get(self::A_TRANS_ID);
        return $result;
    }

    /**
     * @return string
     */
    public function getType()
    {
        $result = parent::get(self::A_TYPE);
        return $result;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        $result = parent::get(self::A_VALUE);
        return $result;
    }


    /**
     * @param string $data
     * @return void
     */
    public function setAsset($data)
    {
        parent::set(self::A_ASSET, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setCustomerId($data)
    {
        parent::set(self::A_CUSTOMER_ID, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setCustomerName($data)
    {
        parent::set(self::A_CUSTOMER_NAME, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setDate($data)
    {
        parent::set(self::A_DATE, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setDetails($data)
    {
        parent::set(self::A_DETAILS, $data);
    }

    /**
     * @param int $data
     * @return void
     */
    public function setTransId($data)
    {
        parent::set(self::A_TRANS_ID, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setType($data)
    {
        parent::set(self::A_TYPE, $data);
    }

    /**
     * @param float $data
     * @return void
     */
    public function setValue($data)
    {
        parent::set(self::A_VALUE, $data);
    }
}