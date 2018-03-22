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
    const A_VALUE = 'value';

    /** @return string */
    public function getAsset()
    {
        $result = parent::get(self::A_ASSET);
        return $result;
    }

    /** @return float */
    public function getValue()
    {
        $result = parent::get(self::A_VALUE);
        return $result;
    }

    /** @param string $data */
    public function setAsset($data)
    {
        parent::set(self::A_ASSET, $data);
    }

    /** @param float $data */
    public function setValue($data)
    {
        parent::set(self::A_VALUE, $data);
    }
}