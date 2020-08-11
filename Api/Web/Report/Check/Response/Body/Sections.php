<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body;

use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus as DInf;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OrgProfile as DOrgProfile;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus as DOver;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\PersonalBonus as DPersonal;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs as DQualLegs;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignupBonus as DSignup;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus as DTeam;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Totals as DTotals;

class Sections
    extends \Praxigento\Core\Data
{
    const A_INFINITY_BONUS = 'infinity_bonus';
    const A_ORG_PROFILE = 'org_profile';
    const A_OVERRIDE_BONUS = 'override_bonus';
    const A_PERSONAL_BONUS = 'personal_bonus';
    const A_QUAL_LEGS = 'qual_legs';
    const A_SIGNUP_BONUS = 'signup_bonus';
    const A_TEAM_BONUS = 'team_bonus';
    const A_TOTALS = 'totals';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus
     */
    public function getInfBonus()
    {
        $result = parent::get(self::A_INFINITY_BONUS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OrgProfile
     */
    public function getOrgProfile()
    {
        $result = parent::get(self::A_ORG_PROFILE);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus
     */
    public function getOverBonus()
    {
        $result = parent::get(self::A_OVERRIDE_BONUS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\PersonalBonus
     */
    public function getPersonalBonus()
    {
        $result = parent::get(self::A_PERSONAL_BONUS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs
     */
    public function getQualLegs()
    {
        $result = parent::get(self::A_QUAL_LEGS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignupBonus
     */
    public function getSignupBonus()
    {
        $result = parent::get(self::A_SIGNUP_BONUS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus
     */
    public function getTeamBonus()
    {
        $result = parent::get(self::A_TEAM_BONUS);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Totals
     */
    public function getTotals()
    {
        $result = parent::get(self::A_TOTALS);
        return $result;
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\InfBonus $data
     * @return void
     */
    public function setInfBonus(DInf $data)
    {
        parent::set(self::A_INFINITY_BONUS, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OrgProfile $data
     * @return void
     */
    public function setOrgProfile(DOrgProfile $data)
    {
        parent::set(self::A_ORG_PROFILE, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\OverBonus $data
     * @return void
     */
    public function setOverBonus(DOver $data)
    {
        parent::set(self::A_OVERRIDE_BONUS, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\PersonalBonus $data
     * @return void
     */
    public function setPersonalBonus(DPersonal $data)
    {
        parent::set(self::A_PERSONAL_BONUS, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs $data
     * @return void
     */
    public function setQualLegs(DQualLegs $data)
    {
        parent::set(self::A_QUAL_LEGS, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignupBonus $data
     * @return void
     */
    public function setSignupBonus(DSignup $data)
    {
        parent::set(self::A_SIGNUP_BONUS, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\TeamBonus $data
     * @return void
     */
    public function setTeamBonus(DTeam $data)
    {
        parent::set(self::A_TEAM_BONUS, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\Totals $data
     * @return void
     */
    public function setTotals(DTotals $data)
    {
        parent::set(self::A_TOTALS, $data);
    }
}
