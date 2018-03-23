<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper;

use Praxigento\Dcp\Config as Cfg;

/**
 * Return 'true' if customer belongs to EU scheme.
 */
class IsSchemeEu
{
    /** @var \Praxigento\BonusHybrid\Helper\IScheme */
    private $hlpScheme;
    /** @var \Praxigento\Downline\Repo\Dao\Customer */
    private $daoDwnlCust;

    public function __construct(
        \Praxigento\BonusHybrid\Helper\IScheme $hlpScheme,
        \Praxigento\Downline\Repo\Dao\Customer $daoDwnlCust
    )
    {
        $this->hlpScheme = $hlpScheme;
        $this->daoDwnlCust = $daoDwnlCust;
    }

    /**
     * Return 'true' if customer belongs to EU scheme.
     *
     * @param int $custId
     * @return bool
     */
    public function exec($custId)
    {
        $custData = $this->daoDwnlCust->getById($custId);
        $scheme = $this->hlpScheme->getSchemeByCustomer($custData);
        $result = ($scheme == Cfg::SCHEMA_EU);
        return $result;
    }
}