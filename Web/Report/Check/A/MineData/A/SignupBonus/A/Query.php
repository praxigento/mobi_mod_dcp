<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2020
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A\SignupBonus\A;

use Praxigento\Accounting\Repo\Data\Transaction as ETrans;
use Praxigento\BonusBase\Repo\Data\Log\Customers as ELogCust;
use Praxigento\BonusBase\Repo\Data\Log\Opers as ELogOper;

class Query
    extends \Praxigento\Core\App\Repo\Query\Builder
{

    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_LOG_CUST = 'logCust';
    const AS_LOG_OPER = 'logOper';
    const AS_TRANS = 'trans';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_AMOUNT = 'amount';
    const A_NOTE = 'note';

    /** Bound variables names ('camelCase' naming) */
    const BND_CALC_ID = 'calcId';
    const BND_CUST_ID = 'custId';

    /** Entities are used in the query */
    const E_LOG_CUST = ELogCust::ENTITY_NAME;
    const E_LOG_OPER = ELogOper::ENTITY_NAME;
    const E_TRANS = ETrans::ENTITY_NAME;

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        /* this is root query builder (started from SELECT) */
        $result = $this->conn->select();

        /* define tables aliases for internal usage (in this method) */
        $asLogCust = self::AS_LOG_CUST;
        $asLogOper = self::AS_LOG_OPER;
        $asTrans = self::AS_TRANS;

        /* FROM prxgt_bon_base_log_opers  */
        $tbl = $this->resource->getTableName(ELogOper::ENTITY_NAME);
        $as = $asLogOper;
        $cols = [];
        $result->from([$as => $tbl], $cols);

        /* JOIN prxgt_acc_transaction */
        $tbl = $this->resource->getTableName(ETrans::ENTITY_NAME);
        $as = $asTrans;
        $cols = [
            self::A_AMOUNT => ETrans::A_VALUE,
            self::A_NOTE => ETrans::A_NOTE
        ];
        $cond = $as . '.' . ETrans::A_OPERATION_ID . '=' . $asLogOper . '.' . ELogOper::A_OPER_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_bon_base_log_cust to get link to bonus donors */
        $tbl = $this->resource->getTableName(ELogCust::ENTITY_NAME);
        $as = $asLogCust;
        $cols = [];
        $cond = $as . '.' . ELogCust::A_TRANS_ID . '=' . $asTrans . '.' . ETrans::A_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* query tuning */
        $byCalcId = "$asLogOper." . ELogOper::A_CALC_ID . '=:' . self::BND_CALC_ID;
        $byCustId = "$asLogCust." . ELogCust::A_CUSTOMER_ID . '=:' . self::BND_CUST_ID;
        $result->where("($byCalcId) AND ($byCustId)");

        return $result;
    }
}
