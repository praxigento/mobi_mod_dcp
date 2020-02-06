<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A\Customer\A;

use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\Downline\Config as Cfg;
use Praxigento\Downline\Repo\Data\Customer as EDwnlCust;
use Praxigento\Downline\Repo\Data\Snap as EDwnlSnap;
use Praxigento\Downline\Repo\Query\Snap\OnDate\Builder as QBBase;

/**
 * Query builder to be used in "Praxigento\Dcp\Api\Web\Report\Check" context
 * to get customer data for the given date.
 */
class Query
    extends \Praxigento\Core\App\Repo\Query\Builder
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_BON_DWNL = 'bonDwnl';
    const AS_DWNL_CUST = 'dwnlCust';
    const AS_MAGE_CUST = 'mageCust';
    const AS_SNAP = QBBase::AS_DWNL_SNAP;

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_CUST_ID = QBBase::A_CUST_ID;
    const A_DEPTH = QBBase::A_DEPTH;
    const A_DEPTH_COMPRESSED = 'depthCompress';
    const A_MLM_ID = 'mlmId';
    const A_NAME_FIRST = 'nameFirst';
    const A_NAME_LAST = 'nameLast';
    const A_PARENT_ID = QBBase::A_PARENT_ID;
    const A_PATH = QBBase::A_PATH;

    /** Bound variables names ('camelCase' naming) */
    const BND_CALC_ID = 'calcId';
    const BND_CUST_ID = 'custId';
    const BND_ON_DATE = QBBase::BND_ON_DATE;

    /** @var \Praxigento\Downline\Repo\Query\Snap\OnDate\Builder */
    private $qbSnap;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Downline\Repo\Query\Snap\OnDate\Builder $qbSnap

    ) {
        parent::__construct($resource);
        $this->qbSnap = $qbSnap;
    }

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        /* build own query based on downline snap query */
        $result = $this->qbSnap->build();

        /* define tables aliases for internal usage (in this method) */
        $asBonDwnl = self::AS_BON_DWNL;
        $asDwnlCust = self::AS_DWNL_CUST;
        $asMageCust = self::AS_MAGE_CUST;
        $asSnap = self::AS_SNAP;

        /* LEFT JOIN prxgt_dwnl_customer to get MLM ID */
        $tbl = $this->resource->getTableName(EDwnlCust::ENTITY_NAME);
        $as = $asDwnlCust;
        $cols = [
            self::A_MLM_ID => EDwnlCust::A_MLM_ID
        ];
        $cond = "$as." . EDwnlCust::A_CUSTOMER_REF . "=$asSnap." . EDwnlSnap::A_CUSTOMER_REF;
        $result->join([$as => $tbl], $cond, $cols);

        /* LEFT JOIN customer_entity to get first/last names */
        $tbl = $this->resource->getTableName(Cfg::ENTITY_MAGE_CUSTOMER);
        $as = $asMageCust;
        $cols = [
            self::A_NAME_FIRST => Cfg::E_CUSTOMER_A_FIRSTNAME,
            self::A_NAME_LAST => Cfg::E_CUSTOMER_A_LASTNAME
        ];
        $cond = "$as." . Cfg::E_CUSTOMER_A_ENTITY_ID . "=$asSnap." . EDwnlSnap::A_CUSTOMER_REF;
        $result->join([$as => $tbl], $cond, $cols);


        /* LEFT JOIN prxgt_bon_hyb_dwnl */
        $tbl = $this->resource->getTableName(EBonDwnl::ENTITY_NAME);
        $as = $asBonDwnl;
        $cols = [
            self::A_DEPTH_COMPRESSED => EBonDwnl::A_DEPTH
        ];
        $cond = "$as." . EBonDwnl::A_CUST_REF . "=$asDwnlCust." . EDwnlCust::A_CUSTOMER_REF;
        $result->join([$as => $tbl], $cond, $cols);

        /* where */
        $byCustId = "$asDwnlCust." . EDwnlCust::A_CUSTOMER_REF . '=:' . self::BND_CUST_ID;
        $byCalcId = "$asBonDwnl." . EBonDwnl::A_CALC_REF . '=:' . self::BND_CALC_ID;
        $result->where("($byCustId) AND ($byCalcId)");

        return $result;
    }

}