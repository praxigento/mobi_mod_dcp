<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections;


class PersonalBonus
    extends \Praxigento\Core\Data
{
    const A_COMPRESSED_VOLUME = 'compressed_volume';
    const A_OWN_VOLUME = 'own_volume';
    const A_PERCENT = 'percent';

    /**
     * @return float
     */
    public function getCompressedVolume()
    {
        $result = parent::get(self::A_COMPRESSED_VOLUME);
        return $result;
    }

    /**
     * @return float
     */
    public function getOwnVolume()
    {
        $result = parent::get(self::A_OWN_VOLUME);
        return $result;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        $result = parent::get(self::A_PERCENT);
        return $result;
    }

    /**
     * @param $data
     * @return void
     */
    public function setCompressedVolume($data)
    {
        parent::set(self::A_COMPRESSED_VOLUME, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setOwnVolume($data)
    {
        parent::set(self::A_OWN_VOLUME, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setPercent($data)
    {
        parent::set(self::A_PERCENT, $data);
    }
}