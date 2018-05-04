<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A\OrgProfile\A;

use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\BonusHybrid\Repo\Data\Downline\Qualification as EBonQual;

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

    /** Entities are used in the query */
    const E_BON_DWNL = EBonDwnl::ENTITY_NAME;
    const E_BON_QUAL = EBonQual::ENTITY_NAME;


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
        $expCountSrc = 'COUNT(' . EBonDwnl::A_CUST_REF . ')';
        $expCount = new \Praxigento\Core\App\Repo\Query\Expression($expCountSrc);
        $expVolumeSrc = 'SUM(' . EBonDwnl::A_PV . ')';
        $expVolume = new \Praxigento\Core\App\Repo\Query\Expression($expVolumeSrc);
        $cols = [
            self::A_DEPTH => EBonDwnl::A_DEPTH,
            self::A_COUNT => $expCount,
            self::A_VOLUME => $expVolume
        ];
        $result->from([$as => $tbl], $cols);

        /* LEFT JOIN prxgt_bon_hyb_dwnl_qual */
        $tbl = $this->resource->getTableName(EBonQual::ENTITY_NAME);
        $as = $asQual;
        /* TODO: do we really need this entity? See \Praxigento\BonusHybrid\Repo\Data\Downline::A_RANK_REF */
        $expMgrSrc = 'COUNT(' . self::AS_BON_DWNL_QUAL . '.' . EBonQual::A_RANK_REF . ')';
        $expMgr = new \Praxigento\Core\App\Repo\Query\Expression($expMgrSrc);
        $cols = [
            self::A_QUAL => $expMgr
        ];
        $cond = $as . '.' . EBonQual::A_TREE_ENTRY_REF . '=' . $asDwnl . '.' . EBonDwnl::A_ID;
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
}