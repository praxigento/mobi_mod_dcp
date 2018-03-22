<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Profile\Response;

/**
 * Data for DCP Distributor Profile report.
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Data
    extends \Praxigento\Core\Data
{
    const A_BALANCES = 'balances';
    const A_BONUS_STATS = 'bonus_stats';
    const A_MLM_ID_OWN = 'mlm_id_own';
    const A_MLM_ID_PARENT = 'mlm_id_parent';
    const A_PENSION = 'pension';

    /** @return \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Balance\Item[] */
    public function getBalances()
    {
        $result = parent::get(self::A_BALANCES);
        return $result;
    }

    /** @return \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\BonusStats */
    public function getBonusStats()
    {
        $result = parent::get(self::A_BONUS_STATS);
        return $result;
    }

    /** @return string */
    public function getMlmIdOwn()
    {
        $result = parent::get(self::A_MLM_ID_OWN);
        return $result;
    }

    /** @return string */
    public function getMlmIdParent()
    {
        $result = parent::get(self::A_MLM_ID_PARENT);
        return $result;
    }

    /** @return \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Pension */
    public function getPension()
    {
        $result = parent::get(self::A_PENSION);
        return $result;
    }

    /** @param \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Balance\Item[] $data */
    public function setBalances($data)
    {
        parent::set(self::A_BALANCES, $data);
    }

    /** @param \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\BonusStats $data */
    public function setBonusStats($data)
    {
        parent::set(self::A_BONUS_STATS, $data);
    }

    /** @param string $data */
    public function setMlmIdOwn($data)
    {
        parent::set(self::A_MLM_ID_OWN, $data);
    }

    /** @param string $data */
    public function setMlmIdParent($data)
    {
        parent::set(self::A_MLM_ID_PARENT, $data);
    }

    /** @param \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Pension $data */
    public function setPension($data)
    {
        parent::set(self::A_PENSION, $data);
    }

}