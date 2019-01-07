<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A\OrgProfile\A;

use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\Core\App\Repo\Query\Expression as AnExpress;

/**
 * Get downline tree data by generations.
 */
class Query
    extends \Praxigento\Core\App\Repo\Query\Builder
{

    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_BON_DWNL = 'dwnl';
    const AS_BON_DWNL_QUAL = 'qual';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_COUNT = 'count';
    const A_DEPTH = 'depth';
    const A_QUAL = 'qual';
    const A_VOLUME = 'volume';

    /** Bound variables names ('camelCase' naming) */
    const BND_CALC_ID = 'calcId';
    const BND_PATH = 'path';
    const BND_PV = 'pv';
    const BND_RANK_ID_UNRANKED = 'rankIdUnranked';

    /** Entities are used in the query */
    const E_BON_DWNL = EBonDwnl::ENTITY_NAME;


    public function build(\Magento\Framework\DB\Select $source = null)
    {
        /* this is root query builder (started from SELECT) */
        $result = $this->conn->select();

        /* define tables aliases for internal usage (in this method) */
        $asDwnl = self::AS_BON_DWNL;
        $asQual = self::AS_BON_DWNL_QUAL;

        /* FROM prxgt_bon_hyb_dwnl  */
        $tbl = $this->resource->getTableName(EBonDwnl::ENTITY_NAME);
        $as = $asDwnl;
        $expCount = $this->expCount();
        $expVolume = $this->expVolume();
        $cols = [
            self::A_DEPTH => EBonDwnl::A_DEPTH,
            self::A_COUNT => $expCount,
            self::A_VOLUME => $expVolume
        ];
        $result->from([$as => $tbl], $cols);

        /* LEFT JOIN prxgt_bon_hyb_dwnl as qual */
        $tbl = $this->resource->getTableName(EBonDwnl::ENTITY_NAME);
        $as = $asQual;
        $expQual = $this->expQual();
        $cols = [
            self::A_QUAL => $expQual
        ];
        $byId = $as . '.' . EBonDwnl::A_ID . '=' . $asDwnl . '.' . EBonDwnl::A_ID;
        $byRankId = $as . '.' . EBonDwnl::A_RANK_REF . '!=:' . self::BND_RANK_ID_UNRANKED;
        $cond = "($byId) AND ($byRankId)";
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* query tuning */
        $byCalcId = "$asDwnl." . EBonDwnl::A_CALC_REF . ' = :' . self::BND_CALC_ID;
        $byPath = "$asDwnl." . EBonDwnl::A_PATH . ' LIKE :' . self::BND_PATH;
        $byPv = "$asDwnl." . EBonDwnl::A_PV . ' > :' . self::BND_PV;
        $result->where("($byCalcId) AND ($byPath) AND ($byPv)");

        /* group by */
        $result->group($asDwnl . '.' . EBonDwnl::A_DEPTH);

        return $result;
    }

    /**
     * Get expression to collect total count of qualified customers in downline.
     *
     * @return \Praxigento\Core\App\Repo\Query\Expression
     */
    private function expCount()
    {
        $exp = 'COUNT(' . self::AS_BON_DWNL . '.' . EBonDwnl::A_CUST_REF . ')';
        $result = new AnExpress($exp);
        return $result;
    }

    /**
     * Get expression to collect total count of qualified customers in downline.
     *
     * @return \Praxigento\Core\App\Repo\Query\Expression
     */
    private function expQual()
    {
        $exp = 'COUNT(' . self::AS_BON_DWNL_QUAL . '.' . EBonDwnl::A_RANK_REF . ')';
        $result = new AnExpress($exp);
        return $result;
    }

    /**
     * Get expression to collect PV volume for all customers in downline.
     *
     * @return \Praxigento\Core\App\Repo\Query\Expression
     */
    private function expVolume()
    {
        $exp = 'SUM(' . self::AS_BON_DWNL . '.' . EBonDwnl::A_PV . ')';
        $result = new AnExpress($exp);
        return $result;
    }
}