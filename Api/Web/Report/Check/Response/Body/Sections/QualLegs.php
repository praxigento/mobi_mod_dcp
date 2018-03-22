<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections;


class QualLegs
    extends \Praxigento\Core\Data
{
    const A_ITEMS = 'items';
    const A_QUALIFICATION = 'qualification';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs\Item[]
     */
    public function getItems()
    {
        $result = parent::get(self::A_ITEMS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs\Qualification
     */
    public function getQualification()
    {
        $result = parent::get(self::A_QUALIFICATION);
        return $result;
    }

    public function setItems($data)
    {
        parent::set(self::A_ITEMS, $data);
    }

    public function setQualification($data)
    {
        parent::set(self::A_QUALIFICATION, $data);
    }

}