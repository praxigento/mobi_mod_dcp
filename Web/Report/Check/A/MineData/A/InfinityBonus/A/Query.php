<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A\InfinityBonus\A;

use Praxigento\Accounting\Repo\Data\Account as EAcc;
use Praxigento\Accounting\Repo\Data\Transaction as ETrans;
use Praxigento\BonusBase\Repo\Data\Log\Customers as ELogCust;
use Praxigento\BonusBase\Repo\Data\Log\Opers as ELogOper;
use Praxigento\BonusBase\Repo\Data\Rank as ERankCode;
use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
use Praxigento\Core\App\Repo\Query\Expression as AnExpress;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Downline\Repo\Data\Customer as EDwnCust;

/**
 * the same as \Praxigento\Dcp\Web\Report\Check\A\MineData\A\OverrideBonus\Db\Query\GetItems
 */
class Query
    extends \Praxigento\Core\App\Repo\Query\Builder
{

    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_ACC = 'acc';
    const AS_BON_DWNL = 'bonDwnl';
    const AS_CUST = 'cust';
    const AS_DWNL_CUST = 'dwnCust';
    const AS_LOG_CUST = 'logCust';
    const AS_LOG_OPER = 'logOper';
    const AS_RANK_CODE = 'rankCode';
    const AS_TRANS = 'trans';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_AMOUNT = 'amount';
    const A_CUST_ID = 'custId';
    const A_DEPTH = 'depth';
    const A_MLM_ID = 'mlmId';
    const A_NAME_FIRST = 'nameFirst';
    const A_NAME_LAST = 'nameLast';
    const A_PV = 'pv';
    const A_RANK_CODE = 'rankCode';

    /** Bound variables names ('camelCase' naming) */
    const BND_CALC_ID_BONUS = 'calcIdCompress';
    const BND_CALC_ID_COMPRESS = 'calcIdBonus';
    const BND_CUST_ID = 'custId';

    /** Entities are used in the query */
    const E_ACC = EAcc::ENTITY_NAME;
    const E_BON_DWNL = EBonDwnl::ENTITY_NAME;
    const E_CUSTOMER = Cfg::ENTITY_MAGE_CUSTOMER;
    const E_DWNL_CUST = EDwnCust::ENTITY_NAME;
    const E_LOG_CUST = ELogCust::ENTITY_NAME;
    const E_LOG_OPER = ELogOper::ENTITY_NAME;
    const E_RANK_CODE = ERankCode::ENTITY_NAME;
    const E_TRANS = ETrans::ENTITY_NAME;


    public function build(\Magento\Framework\DB\Select $source = null)
    {
        /* this is root query builder (started from SELECT) */
        $result = $this->conn->select();

        /* define tables aliases for internal usage (in this method) */
        $asAcc = self::AS_ACC;
        $asBonDwnl = self::AS_BON_DWNL;
        $asCust = self::AS_CUST;
        $asDwnlCust = self::AS_DWNL_CUST;
        $asLogCust = self::AS_LOG_CUST;
        $asLogOper = self::AS_LOG_OPER;
        $asRankCode = self::AS_RANK_CODE;
        $asTrans = self::AS_TRANS;

        /* FROM prxgt_bon_base_log_opers  */
        $tbl = $this->resource->getTableName(ELogOper::ENTITY_NAME);
        $as = $asLogOper;
        $cols = [];
        $result->from([$as => $tbl], $cols);

        /* JOIN prxgt_acc_transaction to get amount & link to accounts */
        $tbl = $this->resource->getTableName(ETrans::ENTITY_NAME);
        $as = $asTrans;
        $cols = [
            self::A_AMOUNT => ETrans::A_VALUE
        ];
        $cond = $as . '.' . ETrans::A_OPERATION_ID . '=' . $asLogOper . '.' . ELogOper::A_OPER_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_acc_account to get link to the customer */
        $tbl = $this->resource->getTableName(EAcc::ENTITY_NAME);
        $as = $asAcc;
        $cols = [];
        $cond = $as . '.' . EAcc::A_ID . '=' . $asTrans . '.' . ETrans::A_CREDIT_ACC_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_bon_base_log_cust to get link to bonus donors */
        $tbl = $this->resource->getTableName(ELogCust::ENTITY_NAME);
        $as = $asLogCust;
        $cols = [
            self::A_CUST_ID => ELogCust::A_CUSTOMER_ID
        ];
        $cond = $as . '.' . ELogCust::A_TRANS_ID . '=' . $asTrans . '.' . ETrans::A_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_dwnl_customer to get MLM IDs for donors */
        $tbl = $this->resource->getTableName(EDwnCust::ENTITY_NAME);
        $as = $asDwnlCust;
        $cols = [
            self::A_MLM_ID => EDwnCust::A_MLM_ID
        ];
        $cond = $as . '.' . EDwnCust::A_CUSTOMER_REF . '=' . $asLogCust . '.' . ELogCust::A_CUSTOMER_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN customer_entity to get name */
        $tbl = $this->resource->getTableName(Cfg::ENTITY_MAGE_CUSTOMER);
        $as = $asCust;
        $cols = [
            self::A_NAME_FIRST => Cfg::E_CUSTOMER_A_FIRSTNAME,
            self::A_NAME_LAST => Cfg::E_CUSTOMER_A_LASTNAME
        ];
        $cond = $as . '.' . Cfg::E_CUSTOMER_A_ENTITY_ID . '=' . $asDwnlCust . '.' . EDwnCust::A_CUSTOMER_REF;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_bon_hyb_dwnl to get PV & depth for donors */
        $tbl = $this->resource->getTableName(EBonDwnl::ENTITY_NAME);
        $as = $asBonDwnl;
        $cols = [
            self::A_DEPTH => EBonDwnl::A_DEPTH,
            self::A_PV => EBonDwnl::A_PV
        ];
        $onCalcRef = $as . '.' . EBonDwnl::A_CALC_REF . '=:' . self::BND_CALC_ID_COMPRESS;
        $onCustId = $as . '.' . EBonDwnl::A_CUST_REF . '=' . $asDwnlCust . '.' . EDwnCust::A_CUSTOMER_REF;
        $cond = "($onCalcRef) AND ($onCustId)";
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* JOIN prxgt_bon_base_rank to get rankCodes for rankIds */
        $tbl = $this->resource->getTableName(ERankCode::ENTITY_NAME);
        $as = $asRankCode;
        $cols = [
            self::A_RANK_CODE => ERankCode::A_CODE
        ];
        $cond = $as . '.' . ERankCode::A_ID . '=' . $asBonDwnl . '.' . EBonDwnl::A_RANK_REF;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* query tuning */
        $byCalcBonus = "$asLogOper." . ELogOper::A_CALC_ID . '=:' . self::BND_CALC_ID_BONUS;
        $byCustId = "$asAcc." . EAcc::A_CUST_ID . '=:' . self::BND_CUST_ID;
        $result->where("($byCalcBonus) AND ($byCustId)");

        $exp = $this->expByPathFull();
        $result->order($exp);

        return $result;
    }

    /**
     * Get expression to order items by absolute path in the downline.
     *
     * @return \Praxigento\Core\App\Repo\Query\Expression
     */
    private function expByPathFull()
    {
        $exp = 'CONCAT(' . self::AS_BON_DWNL . '.' . EBonDwnl::A_PATH . ',' . self::AS_BON_DWNL . '.' . EBonDwnl::A_CUST_REF . ')';
        $result = new AnExpress($exp);
        return $result;
    }
}
