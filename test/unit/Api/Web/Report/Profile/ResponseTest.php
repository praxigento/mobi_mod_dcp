<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Test\Praxigento\Dcp\Api\Web\Report\Profile;

use Praxigento\Dcp\Api\Web\Report\Profile\Response as AnObject;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class ResponseTest
    extends \Praxigento\Core\Test\BaseCase\Unit
{

    private function getBalances()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Balance\Item();
        $result->setAsset('PV');
        $result->setCurrency('LVL');
        $result->setValue(21.34);
        return [$result];
    }

    private function getBonusStats()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\BonusStats();
        $result->setDateUpdated('date');
        $result->setOv(10.10);
        $result->setPv(20.20);
        $result->setRank('rank');
        $result->setTv(30.30);
        return $result;
    }

    private function getPension()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Pension();
        $result->setMonthLeft(1);
        $result->setMonthSince('YYYYMM');
        $result->setMonthTotal(2);
        $result->setMonthUnq(3);
        return $result;
    }

    public function test_convert()
    {
        /* create object & convert it to 'JSON'-array */
        $obj = new AnObject();
        $balances = $this->getBalances();
        $bonusStats = $this->getBonusStats();
        $pension = $this->getPension();

        $data = new \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data();
        $data->setBalances($balances);
        $data->setBonusStats($bonusStats);
        $data->setMlmIdOwn('own');
        $data->setMlmIdParent('parent');
        $data->setPension($pension);
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