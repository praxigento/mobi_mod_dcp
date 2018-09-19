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
class Customer
    extends \Praxigento\Core\Data
{
    const A_ID = 'id';
    const A_MLM_ID = 'mlm_id';
    const A_NAME_FIRST = 'name_first';
    const A_NAME_LAST = 'name_last';

    /**
     * @return int
     */
    public function getId()
    {
        $result = parent::get(self::A_ID);
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
     * @param int $data
     * @return void
     */
    public function setId($data)
    {
        parent::set(self::A_ID, $data);
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
}