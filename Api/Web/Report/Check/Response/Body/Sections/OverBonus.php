<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections;


class OverBonus
    extends \Praxigento\Core\Data
{
    const A_ITEMS = 'items';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus\Item[]
     */
    public function getItems()
    {
        $result = parent::get(self::A_ITEMS);
        return $result;
    }

    /**
     * @param $data
     * @return void
     */
    public function setItems($data)
    {
        parent::set(self::A_ITEMS, $data);
    }

}