<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections;


class OrgProfile
    extends \Praxigento\Core\Data
{
    const A_ITEMS = 'items';

    /**
     * @return \Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections\OrgProfile\Item[]
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