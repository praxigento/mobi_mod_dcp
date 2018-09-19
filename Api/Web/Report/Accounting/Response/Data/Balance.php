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
class Balance
    extends \Praxigento\Core\Data
{
    const A_ASSET = 'asset';
    const A_CURRENCY = 'currency';
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
     * @return string|null
     */
    public function getCurrency()
    {
        $result = parent::get(self::A_CURRENCY);
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
     * @return null
     */
    public function setAsset($data)
    {
        parent::set(self::A_ASSET, $data);
    }

    /**
     * @param string $data
     * @return null
     */
    public function setCurrency($data)
    {
        parent::set(self::A_CURRENCY, $data);
    }

    /**
     * @param string $data
     * @return null
     */
    public function setValue($data)
    {
        parent::set(self::A_VALUE, $data);
    }
}