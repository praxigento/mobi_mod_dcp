<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections;


class InfBonus
    extends \Praxigento\Core\Data
{
    const A_ITEMS = 'items';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus\Item[]
     */
    public function getItems()
    {
        $result = parent::get(self::A_ITEMS);
        return $result;
    }


    public function setItems($data)
    {
        parent::set(self::A_ITEMS, $data);
    }

}