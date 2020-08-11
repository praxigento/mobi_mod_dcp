<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections;


class Totals
    extends \Praxigento\Core\Data
{
    const A_COURTESY_AMOUNT = 'courtesy_amount';
    const A_INFINITY_AMOUNT = 'infinity_amount';
    const A_NET_AMOUNT = 'net_amount';
    const A_OVERRIDE_AMOUNT = 'override_amount';
    const A_PERSONAL_AMOUNT = 'personal_amount';
    const A_PROCESSING_FEE = 'processing_fee';
    const A_SIGNUP_AMOUNT = 'signup_amount';
    const A_TEAM_AMOUNT = 'team_amount';
    const A_TOTAL_AMOUNT = 'total_amount';

    /**
     * @return float
     */
    public function getCourtesyAmount()
    {
        $result = parent::get(self::A_COURTESY_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getInfinityAmount()
    {
        $result = parent::get(self::A_INFINITY_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getNetAmount()
    {
        $result = parent::get(self::A_NET_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getOverrideAmount()
    {
        $result = parent::get(self::A_OVERRIDE_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getPersonalAmount()
    {
        $result = parent::get(self::A_PERSONAL_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getProcessingFee()
    {
        $result = parent::get(self::A_PROCESSING_FEE);
        return $result;
    }

    /**
     * @return float
     */
    public function getSignupAmount()
    {
        $result = parent::get(self::A_SIGNUP_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getTeamAmount()
    {
        $result = parent::get(self::A_TEAM_AMOUNT);
        return $result;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        $result = parent::get(self::A_TOTAL_AMOUNT);
        return $result;
    }

    /**
     * @param $data
     * @return void
     */
    public function setCourtesyAmount($data)
    {
        parent::set(self::A_COURTESY_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setInfinityAmount($data)
    {
        parent::set(self::A_INFINITY_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setNetAmount($data)
    {
        parent::set(self::A_NET_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setOverrideAmount($data)
    {
        parent::set(self::A_OVERRIDE_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setPersonalAmount($data)
    {
        parent::set(self::A_PERSONAL_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setProcessingFee($data)
    {
        parent::set(self::A_PROCESSING_FEE, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setSignupAmount($data)
    {
        parent::set(self::A_SIGNUP_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setTeamAmount($data)
    {
        parent::set(self::A_TEAM_AMOUNT, $data);
    }

    /**
     * @param $data
     * @return void
     */
    public function setTotalAmount($data)
    {
        parent::set(self::A_TOTAL_AMOUNT, $data);
    }

}
