<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\A;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Context as AContext;
use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body\Sections as DSections;

/**
 * Process step to mine requested data from DB.
 */
class MineData
{
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer */
    private $ownCustomer;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\InfinityBonus */
    private $ownInfBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OrgProfile */
    private $ownOrgProfile;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OverrideBonus */
    private $ownOverBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Pension */
    private $ownPension;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\PersBonus */
    private $ownPersBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\QualLegs */
    private $ownQualLegs;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\TeamBonus */
    private $ownTeamBonus;
    /** @var \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Totals */
    private $ownTotals;

    public function __construct(
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Customer $ownCustomer,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\InfinityBonus $ownInfBonus,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OrgProfile $ownOrgProfile,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\OverrideBonus $ownOverBonus,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Pension $ownPension,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\PersBonus $ownPersBonus,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\QualLegs $ownQualLegs,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\TeamBonus $ownTeamBonus,
        \Praxigento\Dcp\Web\Dcp\Report\Check\A\MineData\A\Totals $ownTotals
    )
    {
        $this->ownCustomer = $ownCustomer;
        $this->ownInfBonus = $ownInfBonus;
        $this->ownOrgProfile = $ownOrgProfile;
        $this->ownOverBonus = $ownOverBonus;
        $this->ownPension = $ownPension;
        $this->ownPersBonus = $ownPersBonus;
        $this->ownQualLegs = $ownQualLegs;
        $this->ownTeamBonus = $ownTeamBonus;
        $this->ownTotals = $ownTotals;
    }

    public function exec(AContext $ctx): AContext
    {
        /* if current instance is active */
        if ($ctx->state == AContext::DEF_STATE_ACTIVE) {
            /* get step's local data from the context */
            $custId = $ctx->getCustomerId();
            $period = $ctx->getPeriod();

            /* perform processing */
            $customer = $this->ownCustomer->exec($custId, $period);
            $infBonus = $this->ownInfBonus->exec($custId, $period);
            $orgProfile = $this->ownOrgProfile->exec($custId, $period);
            $overBonus = $this->ownOverBonus->exec($custId, $period);
            $pension = $this->ownPension->exec($custId, $period);
            $persBonus = $this->ownPersBonus->exec($custId, $period);
            $qualLegs = $this->ownQualLegs->exec($custId, $period);
            $teamBonus = $this->ownTeamBonus->exec($custId, $period);
            $totals = $this->ownTotals->exec($custId, $period);

            /* put result data into context */
            $ctx->respCustomer = $customer;
            $sections = new DSections();
            $sections->setInfBonus($infBonus);
            $sections->setOrgProfile($orgProfile);
            $sections->setOverBonus($overBonus);
            $sections->setPension($pension);
            $sections->setPersonalBonus($persBonus);
            $sections->setQualLegs($qualLegs);
            $sections->setTeamBonus($teamBonus);
            $sections->setTotals($totals);
            $ctx->respSections = $sections;
        }
        return $ctx;
    }
}