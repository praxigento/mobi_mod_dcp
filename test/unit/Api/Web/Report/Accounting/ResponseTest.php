<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Test\Praxigento\Dcp\Api\Web\Report\Accounting;

use Praxigento\Dcp\Api\Web\Report\Accounting\Response as AnObject;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class ResponseTest
    extends \Praxigento\Core\Test\BaseCase\Unit
{

    public function test_convert()
    {
        /* create object & convert it to 'JSON'-array */
        $obj = new AnObject();

        $balClose = new \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance();
        $balClose->setAsset('PV');
        $balClose->setCurrency('LVL');
        $balClose->setValue(1000);

        $balOpen = new \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance();
        $balOpen->setAsset('PV');
        $balOpen->setCurrency('LVL');
        $balOpen->setValue(0);

        $cust = new \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer();
        $cust->setId(8);
        $cust->setMlmId('MLM ID');
        $cust->setNameFirst('first');
        $cust->setNameLast('last');

        $trans = new \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans();
        $trans->setAsset('PVP');
        $trans->setCustomerId(8);
        $trans->setDate('date');
        $trans->setDetails('details');
        $trans->setTransId(4);
        $trans->setType('type');
        $trans->setValue(1000.32);

        $data = new \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data();
        $data->setBalanceClose([$balClose]);
        $data->setBalanceOpen([$balOpen]);
        $data->setCurrency('LVL');
        $data->setCustomer($cust);
        $data->setTrans([$trans]);
        $obj->setData($data);

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