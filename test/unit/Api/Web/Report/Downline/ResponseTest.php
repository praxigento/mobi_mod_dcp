<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Test\Praxigento\Dcp\Api\Web\Report\Downline;

use Praxigento\Dcp\Api\Web\Report\Downline\Response as AnObject;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class ResponseTest
    extends \Praxigento\Core\Test\BaseCase\Unit
{

    public function test_convert()
    {
        /* create object & convert it to 'JSON'-array */
        $obj = new AnObject();

        $entry = new \Praxigento\Dcp\Api\Web\Report\Downline\Response\Entry();
        $entry->setCountry('LV');
        $entry->setCustomerRef(32);
        $entry->setDepth(4);
        $entry->setEmail('email');
        $entry->setMlmId('mlm_id');
        $entry->setNameFirst('first');
        $entry->setNameLast('last');
        $entry->setOv(512.32);
        $entry->setParentRef(64);
        $entry->setPath(':1:2:3');
        $entry->setPv(43.23);
        $entry->setRankCode('rank');
        $entry->setTv(54.65);
        $entry->setUnqMonths(54);
        $obj->setData([$entry]);

        /** @var \Magento\Framework\Webapi\ServiceOutputProcessor $output */
        $output = $this->manObj->get(\Magento\Framework\Webapi\ServiceOutputProcessor::class);
        $json = $output->convertValue($obj, AnObject::class);

        /* convert 'JSON'-array to object */
        /** @var \Magento\Framework\Webapi\ServiceInputProcessor $input */
        $input = $this->manObj->get(\Magento\Framework\Webapi\ServiceInputProcessor::class);
        $data = $input->convertValue($json, AnObject::class);
        $this->assertNotNull($data);
    }
}