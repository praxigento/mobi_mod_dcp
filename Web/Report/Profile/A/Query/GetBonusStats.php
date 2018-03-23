<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Profile\A\Query;

use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ETypeCalc;
use Praxigento\Dcp\Config as Cfg;

/**
 * Get bonus stats from the last complete 'HYBRID_FORECAST_PHASE1' calculation.
 */
class GetBonusStats
    extends \Praxigento\Core\App\Repo\Query\Builder
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_CALC = 'c';
    const AS_CALC_TYPE = 'ct';
    const AS_PERIOD = 'p';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_CALC_ID = 'calcId';
    const A_DATE_UPDATED = 'dateUpdated';

    /** Entities are used in the query */
    const E_CALC = ECalc::ENTITY_NAME;
    const E_CALC_TYPE = ETypeCalc::ENTITY_NAME;
    const E_PERIOD = EPeriod::ENTITY_NAME;

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        $result = $this->conn->select();
        /* define tables aliases for internal usage (in this method) */
        $asCalc = self::AS_CALC;
        $asPeriod = self::AS_PERIOD;
        $asType = self::AS_CALC_TYPE;

        /* FROM prxgt_bon_base_type_calc */
        $tbl = $this->resource->getTableName(self::E_CALC_TYPE);
        $as = $asType;
        $cols = [];
        $result->from([$as => $tbl], $cols);

        /* JOIN prxgt_bon_base_period */
        $tbl = $this->resource->getTableName(self::E_PERIOD);
        $as = $asPeriod;
        $cols = [];
        $cond = "$as." . EPeriod::A_CALC_TYPE_ID . '=' . $asType . '.' . ETypeCalc::A_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_bon_base_calc */
        $tbl = $this->resource->getTableName(self::E_CALC);
        $as = $asCalc;
        $cols = [
            self::A_CALC_ID => ECalc::A_ID,
            self::A_DATE_UPDATED => ECalc::A_DATE_ENDED
        ];
        $cond = "$as." . ECalc::A_PERIOD_ID . '=' . $asPeriod . '.' . EPeriod::A_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* add filters */
        $quoted = $this->conn->quote(Cfg::CODE_TYPE_CALC_FORECAST_PHASE1);
        $byCalcCode = "$asType." . ETypeCalc::A_CODE . "=$quoted";
        $quoted = $this->conn->quote(Cfg::CALC_STATE_COMPLETE);
        $byState = "$asCalc." . ECalc::A_STATE . "=$quoted";
        $result->where("($byCalcCode) AND ($byState)");

        /* add order */
        $result->order("$asPeriod." . EPeriod::A_DSTAMP_END . ' DESC');

        /* limit */
        $result->limit(1);

        return $result;
    }
}