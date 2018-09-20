<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Test\Praxigento\Dcp\Api\Web\Report\Check;

use Praxigento\Dcp\Api\Web\Report\Check\Response as AnObject;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class ResponseTest
    extends \Praxigento\Core\Test\BaseCase\Unit
{

    private function getBodyCustomer()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer();
        $result->setId(8);
        $result->setLevel(4);
        $result->setMlmId('MLM ID');
        $result->setName('Customer Name');
        $result->setRank('Dstr');
        return $result;
    }

    private function getBodySections()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections();
        $inf = $this->getSectionInf();
        $org = $this->getSectionOrg();
        $over = $this->getSectionOver();
        $pers = $this->getSectionPers();
        $qual = $this->getSectionLegs();
        $team = $this->getSectionTeam();
        $totals = $this->getSectionTotals();

        $result->setInfBonus($inf);
        $result->setOrgProfile($org);
        $result->setOverBonus($over);
        $result->setPersonalBonus($pers);
        $result->setQualLegs($qual);
        $result->setTeamBonus($team);
        $result->setTotals($totals);
        return $result;
    }

    private function getSectionInf()
    {
        $customer = $this->getBodyCustomer();
        $item = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus\Item();
        $item->setAmount(12.34);
        $item->getCustomer($customer);
        $item->setPercent(0.32);
        $item->setRank('Rank');
        $item->setVolume(1024.64);
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus();
        $result->setItems([$item]);
        return $result;
    }

    private function getSectionLegs()
    {
        $items = $this->getSectionLegsItems();
        $qual = $this->getSectionLegsQual();
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs();
        $result->setItems($items);
        $result->setQualification($qual);
        return $result;
    }

    private function getSectionLegsItems()
    {
        $customer = $this->getBodyCustomer();
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs\Item();
        $result->setCustomer($customer);
        $result->setVolume(32.45);
        return [$result];
    }

    private function getSectionLegsQual()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs\Qualification();
        $result->setMaxLegCustId(16);
        $result->setMaxLegOv(32.64);
        $result->setMaxLegQual(64.32);
        $result->setOtherLegsOv(1024.32);
        $result->setOtherLegsQual(2401.32);
        $result->setSecondLegCust(32);
        $result->setSecondLegOv(32.32);
        $result->setSecondLegQual(64.64);
        return [$result];
    }

    private function getSectionOrg()
    {
        $item = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OrgProfile\Item();
        $item->setActive(4);
        $item->getGeneration(4);
        $item->setInactive(0);
        $item->setMgr(10);
        $item->setMgrAvg(110);
        $item->setQual(220);
        $item->setTotal(330);
        $item->setVolume(1234.56);
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OrgProfile();
        $result->setItems([$item]);
        return $result;
    }

    private function getSectionOver()
    {
        $customer = $this->getBodyCustomer();
        $item = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus\Item();
        $item->setAmount(12.34);
        $item->getCustomer($customer);
        $item->setPercent(0.32);
        $item->setRank('Rank');
        $item->setVolume(1024.64);
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus();
        $result->setItems([$item]);
        return $result;
    }

    private function getSectionPers()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\PersonalBonus();
        $result->setCompressedVolume(12.34);
        $result->setOwnVolume(10.21);
        $result->setPercent(.23);
        return $result;
    }

    private function getSectionTeam()
    {
        $items = $this->getSectionTeamItems();
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus();
        $result->setItems($items);
        $result->setPercent(0.25);
        $result->setTotalVolume(1024);
        return $result;
    }

    private function getSectionTeamItems()
    {
        $customer = $this->getBodyCustomer();
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus\Item();
        $result->setAmount(32.34);
        $result->setCustomer($customer);
        $result->setVolume(1024.45);
        return [$result];
    }

    private function getSectionTotals()
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Totals();
        $result->setCourtesyAmount(10.01);
        $result->setInfinityAmount(20.02);
        $result->setNetAmount(30.03);
        $result->setOverrideAmount(40.04);
        $result->setPersonalAmount(50.05);
        $result->setProcessingFee(60.06);
        $result->setTeamAmount(70.07);
        $result->setTotalAmount(80.08);
        return $result;
    }

    public function test_convert()
    {
        /* create object & convert it to 'JSON'-array */
        $obj = new AnObject();

        $data = new \Praxigento\Dcp\Api\Web\Report\Check\Response\Body();
        $customer = $this->getBodyCustomer();
        $sections = $this->getBodySections();
        $data->setCustomer($customer);
        $data->setCurrency('LVL');
        $data->setPeriod('YYYYMM');
        $data->setSections($sections);
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