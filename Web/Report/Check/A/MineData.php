<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A;

use Praxigento\Dcp\Api\Web\Report\Check\Context as AContext;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections as DSections;

/**
 * Process step to mine requested data from DB.
 */
class MineData
{
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Customer */
    private $aCustomer;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\InfinityBonus */
    private $aInfBonus;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\OrgProfile */
    private $aOrgProfile;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\OverrideBonus */
    private $aOverBonus;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\PersBonus */
    private $aPersBonus;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\QualLegs */
    private $aQualLegs;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\SignUpBonus */
    private $aSignupBonus;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\TeamBonus */
    private $aTeamBonus;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals */
    private $aTotals;

    public function __construct(
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Customer $aCustomer,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\InfinityBonus $aInfBonus,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\OrgProfile $aOrgProfile,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\OverrideBonus $aOverBonus,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\PersBonus $aPersBonus,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\QualLegs $aQualLegs,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\SignUpBonus $aSignupBonus,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\TeamBonus $aTeamBonus,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Totals $aTotals
    ) {
        $this->aCustomer = $aCustomer;
        $this->aInfBonus = $aInfBonus;
        $this->aOrgProfile = $aOrgProfile;
        $this->aOverBonus = $aOverBonus;
        $this->aPersBonus = $aPersBonus;
        $this->aQualLegs = $aQualLegs;
        $this->aSignupBonus = $aSignupBonus;
        $this->aTeamBonus = $aTeamBonus;
        $this->aTotals = $aTotals;
    }

    public function exec(AContext $ctx): AContext
    {
        /* if current instance is active */
        if ($ctx->state == AContext::DEF_STATE_ACTIVE) {
            /* get step's local data from the context */
            $custId = $ctx->getCustomerId();
            $period = $ctx->getPeriod();

            /* perform processing */
            $customer = $this->aCustomer->exec($custId, $period);
            $infBonus = $this->aInfBonus->exec($custId, $period);
            $orgProfile = $this->aOrgProfile->exec($custId, $period);
            $overBonus = $this->aOverBonus->exec($custId, $period);
            $persBonus = $this->aPersBonus->exec($custId, $period);
            $qualLegs = $this->aQualLegs->exec($custId, $period);
            $signupBonus = $this->aSignupBonus->exec($custId, $period);
            $teamBonus = $this->aTeamBonus->exec($custId, $period);
            $totals = $this->aTotals->exec($custId, $period);
            // add sign up to totals
            $amntSignup = $signupBonus->getTotalAmount();
            $amntTotal = $totals->getTotalAmount();
            $amntNet = $totals->getNetAmount();
            $totals->setSignupAmount($amntSignup);
            $totals->setTotalAmount($amntTotal + $amntSignup);
            $totals->setNetAmount($amntNet + $amntSignup);

            /* put result data into context */
            $ctx->respCustomer = $customer;
            $sections = new DSections();
            $sections->setInfBonus($infBonus);
            $sections->setOrgProfile($orgProfile);
            $sections->setOverBonus($overBonus);
            $sections->setPersonalBonus($persBonus);
            $sections->setQualLegs($qualLegs);
            $sections->setSignupBonus($signupBonus);
            $sections->setTeamBonus($teamBonus);
            $sections->setTotals($totals);
            $ctx->respSections = $sections;
        }
        return $ctx;
    }
}
