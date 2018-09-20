<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Balance;

/**
 * Asset balance data for DCP Distributor Profile report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Item
    extends \Praxigento\Core\Data
{
    const A_ASSET = 'asset';
    const A_CURRENCY = 'currency';
    const A_VALUE = 'value';

    /** @return string */
    public function getAsset()
    {
        $result = parent::get(self::A_ASSET);
        return $result;
    }

    /** @return string|null */
    public function getCurrency()
    {
        $result = parent::get(self::A_CURRENCY);
        return $result;
    }

    /** @return float */
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
    public function setCurrency($data)
    {
        parent::set(self::A_CURRENCY, $data);
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