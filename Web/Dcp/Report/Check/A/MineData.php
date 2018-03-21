<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\A;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Context as AContext;
use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections as DSections;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer as SubCustomer;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\InfinityBonus as SubInfBonus;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OrgProfile as SubOrgProfile;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OverrideBonus as SubOverBonus;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\PersBonus as SubPersBonus;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\QualLegs as SubQualLegs;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\TeamBonus as SubTeamBonus;
use Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Totals as SubTotals;

/**
 * Process step to mine requested data from DB.
 */
class MineData
{
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer */
    private $subCustomer;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\InfinityBonus */
    private $subInfBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OrgProfile */
    private $subOrgProfile;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OverrideBonus */
    private $subOverBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\PersBonus */
    private $subPersBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\QualLegs */
    private $subQualLegs;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\TeamBonus */
    private $subTeamBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Totals */
    private $subTotals;

    public function __construct(
        SubCustomer $subCustomer,
        SubInfBonus $subInfBonus,
        SubOrgProfile $subOrgProfile,
        SubOverBonus $subOverBonus,
        SubPersBonus $subPersBonus,
        SubQualLegs $subQualLegs,
        SubTeamBonus $subTeamBonus,
        SubTotals $subTotals
    )
    {
        $this->subCustomer = $subCustomer;
        $this->subInfBonus = $subInfBonus;
        $this->subOrgProfile = $subOrgProfile;
        $this->subOverBonus = $subOverBonus;
        $this->subPersBonus = $subPersBonus;
        $this->subQualLegs = $subQualLegs;
        $this->subTeamBonus = $subTeamBonus;
        $this->subTotals = $subTotals;
    }

    public function exec(AContext $ctx): AContext
    {
        /* if current instance is active */
        if ($ctx->state == AContext::DEF_STATE_ACTIVE) {
            /* get step's local data from the context */
            $custId = $ctx->getCustomerId();
            $period = $ctx->getPeriod();

            /* perform processing */
            $customer = $this->subCustomer->exec($custId, $period);
            $persBonus = $this->subPersBonus->exec($custId, $period);
            $teamBonus = $this->subTeamBonus->exec($custId, $period);
            $qualLegs = $this->subQualLegs->exec($custId, $period);
            $overBonus = $this->subOverBonus->exec($custId, $period);
            $infBonus = $this->subInfBonus->exec($custId, $period);
            $totals = $this->subTotals->exec($custId, $period);
            $orgProfile = $this->subOrgProfile->exec($custId, $period);

            /* put result data into context */
            $ctx->respCustomer = $customer;
            $sections = new DSections();
            $sections->setPersonalBonus($persBonus);
            $sections->setTeamBonus($teamBonus);
            $sections->setQualLegs($qualLegs);
            $sections->setOverBonus($overBonus);
            $sections->setInfBonus($infBonus);
            $sections->setTotals($totals);
            $sections->setOrgProfile($orgProfile);
            $ctx->respSections = $sections;
        }
        return $ctx;
    }
}