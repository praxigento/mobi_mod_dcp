<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Downline\A;

use Praxigento\BonusBase\Repo\Data\Rank as ERank;
use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\BonusHybrid\Repo\Data\Downline\Inactive as EBonDwnlInact;
use Praxigento\Dcp\Api\Web\Report\Downline\Response\Entry as DReportEntry;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Downline\Repo\Data\Customer as EDwnlCust;

/**
 * Build query to get DCP Downline Report data.
 */
class Query
    extends \Praxigento\Core\App\Repo\Query\Builder
{
    /** Tables aliases */
    const AS_BONUS_DWNL = 'bdwnl';
    const AS_DWNL_CUSTOMER = 'dcust';
    const AS_INACT = 'inact';
    const AS_MAGE_CUSTOMER = 'mcust';
    const AS_RANK = 'rank';

    /** Columns aliases */
    const A_COUNTRY = DReportEntry::A_COUNTRY;
    const A_CUSTOMER_REF = DReportEntry::A_CUSTOMER_REF;
    const A_DEPTH = DReportEntry::A_DEPTH;
    const A_EMAIL = DReportEntry::A_EMAIL;
    const A_MLM_ID = DReportEntry::A_MLM_ID;
    const A_NAME_FIRST = DReportEntry::A_NAME_FIRST;
    const A_NAME_LAST = DReportEntry::A_NAME_LAST;
    const A_OV = DReportEntry::A_OV;
    const A_PARENT_REF = DReportEntry::A_PARENT_REF;
    const A_PATH = DReportEntry::A_PATH;
    const A_PV = DReportEntry::A_PV;
    const A_RANK_CODE = DReportEntry::A_RANK_CODE;
    const A_TV = DReportEntry::A_TV;
    const A_UNQ_MONTHS = DReportEntry::A_UNQ_MONTHS;

    /** Bound variables names ('camelCase' naming) */
    const BND_CALC_ID = 'calcId';
    const BND_CUST_ID = 'custId';
    const BND_PATH = 'path';

    /** Entities are used in the query */
    const E_BONUS_DWNL = EBonDwnl::ENTITY_NAME;
    const E_DWN_CUSTOMER = EDwnlCust::ENTITY_NAME;
    const E_MAGE_CUSTOMER = Cfg::ENTITY_MAGE_CUSTOMER;
    const E_INACT = EBonDwnlInact::ENTITY_NAME;
    const E_RANK = ERank::ENTITY_NAME;

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        $result = $this->conn->select();
        /* define tables aliases for internal usage (in this method) */
        $asBonDwnl = self::AS_BONUS_DWNL;
        $asDwnlCust = self::AS_DWNL_CUSTOMER;
        $asMageCust = self::AS_MAGE_CUSTOMER;
        $asInact = self::AS_INACT;
        $asRank = self::AS_RANK;

        /* FROM prxgt_bon_hyb_dwnl */
        $tbl = $this->resource->getTableName(self::E_BONUS_DWNL);
        $as = $asBonDwnl;
        $cols = [
            self::A_CUSTOMER_REF => EBonDwnl::A_CUST_REF,
            self::A_DEPTH => EBonDwnl::A_DEPTH,
            self::A_OV => EBonDwnl::A_OV,
            self::A_PARENT_REF => EBonDwnl::A_PARENT_REF,
            self::A_PATH => EBonDwnl::A_PATH,
            self::A_PV => EBonDwnl::A_PV,
            self::A_TV => EBonDwnl::A_TV
        ];
        $result->from([$as => $tbl], $cols);

        /* LEFT JOIN prxgt_dwnl_customer */
        $tbl = $this->resource->getTableName(self::E_DWN_CUSTOMER);
        $as = $asDwnlCust;
        $cols = [
            self::A_COUNTRY => EDwnlCust::A_COUNTRY_CODE,
            self::A_MLM_ID => EDwnlCust::A_MLM_ID
        ];
        $cond = $as . '.' . EDwnlCust::A_CUSTOMER_ID . '=' . $asBonDwnl . '.' . EBonDwnl::A_CUST_REF;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN customer_entity */
        $tbl = $this->resource->getTableName(self::E_MAGE_CUSTOMER);
        $as = $asMageCust;
        $cols = [
            self::A_EMAIL => Cfg::E_CUSTOMER_A_EMAIL,
            self::A_NAME_FIRST => Cfg::E_CUSTOMER_A_FIRSTNAME,
            self::A_NAME_LAST => Cfg::E_CUSTOMER_A_LASTNAME
        ];
        $cond = $as . '.' . Cfg::E_CUSTOMER_A_ENTITY_ID . '=' . $asBonDwnl . '.' . EBonDwnl::A_CUST_REF;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_bon_hyb_dwnl_inact */
        $tbl = $this->resource->getTableName(self::E_INACT);
        $as = $asInact;
        $cols = [
            self::A_UNQ_MONTHS => EBonDwnlInact::A_INACT_MONTHS
        ];
        $cond = $as . '.' . EBonDwnlInact::A_TREE_ENTRY_REF . '=' . $asBonDwnl . '.' . EBonDwnl::A_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_bon_base_rank */
        $tbl = $this->resource->getTableName(self::E_RANK);
        $as = $asRank;
        $cols = [
            self::A_RANK_CODE => ERank::A_CODE
        ];
        $cond = $as . '.' . ERank::A_ID . '=' . $asBonDwnl . '.' . EBonDwnl::A_RANK_REF;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* query tuning */
        $byCalcId = $asBonDwnl . '.' . EBonDwnl::A_CALC_REF . '=:' . self::BND_CALC_ID;
        $byPath = $asBonDwnl . '.' . EBonDwnl::A_PATH . ' LIKE :' . self::BND_PATH;
        $byCustId = $asBonDwnl . '.' . EBonDwnl::A_CUST_REF . '=:' . self::BND_CUST_ID;
        $result->where("$byCalcId AND ($byPath OR $byCustId)");

        return $result;
    }


}