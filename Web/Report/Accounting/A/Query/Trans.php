<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Accounting\A\Query;

use Praxigento\Accounting\Repo\Data\Account as EAcc;
use Praxigento\Accounting\Repo\Data\Operation as EOper;
use Praxigento\Accounting\Repo\Data\Transaction as ETran;
use Praxigento\Accounting\Repo\Data\Type\Asset as ETypeAsset;
use Praxigento\Accounting\Repo\Data\Type\Operation as ETypeOper;
use Praxigento\Downline\Repo\Data\Customer as EDwnlCust;

/**
 * Build query to get transactions data for DCP Accounting Report.
 */
class Trans
    extends \Praxigento\Core\App\Repo\Query\Builder
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_ACC_CUST = 'acc';
    const AS_ACC_OTHER = 'accOther';
    const AS_ASSET_TYPE = 'assType';
    const AS_DWNL_CUST = 'dwnlCust';
    const AS_DWNL_OTHER = 'dwnlOther';
    const AS_OPER = 'opr';
    const AS_OPER_TYPE = 'oprType';
    const AS_TRAN = 'trn';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_ACC_DEBIT = 'accDebit';
    const A_ACC_OWN = 'accOwn';
    const A_ASSET = 'asset';
    const A_ASSET_CUR = 'assetCur';
    const A_DATE = 'date';
    const A_DETAILS = 'details';
    const A_ITEM_ID = 'itemId';
    const A_OTHER_CUST = 'otherCust';
    const A_TYPE = 'type';
    const A_VALUE = 'value';

    /** Bound variables names ('camelCase' naming) */
    const BND_CUST_ID = 'custId';
    const BND_DATE_FROM = 'dateFrom';
    const BND_DATE_TO = 'dateTo';

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        /* this is root query builder (started from SELECT) */
        $result = $this->conn->select();

        /* define tables aliases for internal usage (in this method) */
        $asAccCust = self::AS_ACC_CUST;
        $asAccOther = self::AS_ACC_OTHER;
        $asAssType = self::AS_ASSET_TYPE;
        $asDwnlCust = self::AS_DWNL_CUST;
        $asDwnlOther = self::AS_DWNL_OTHER;
        $asOper = self::AS_OPER;
        $asOperType = self::AS_OPER_TYPE;
        $asTrans = self::AS_TRAN;

        /* FROM prxgt_dwnl_customer */
        $tbl = $this->resource->getTableName(EDwnlCust::ENTITY_NAME);
        $as = $asDwnlCust;
        $cols = [];
        $result->from([$as => $tbl], $cols);    // standard names for the variables

        /* LEFT JOIN prxgt_acc_account */
        $tbl = $this->resource->getTableName(EAcc::ENTITY_NAME);
        $as = $asAccCust;
        $cols = [
            self::A_ACC_OWN => EAcc::A_ID
        ];
        $cond = "$as." . EAcc::A_CUST_ID . "=$asDwnlCust." . EDwnlCust::A_CUSTOMER_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_transaction */
        $tbl = $this->resource->getTableName(ETran::ENTITY_NAME);
        $as = $asTrans;
        $cols = [
            self::A_ACC_DEBIT => ETran::A_DEBIT_ACC_ID,
            self::A_ITEM_ID => ETran::A_ID,
            self::A_DATE => ETran::A_DATE_APPLIED,
            self::A_DETAILS => ETran::A_NOTE,
            self::A_VALUE => ETran::A_VALUE
        ];
        $condDeb = "$as." . ETran::A_DEBIT_ACC_ID . "=$asAccCust." . EAcc::A_ID;
        $condCred = "$as." . ETran::A_CREDIT_ACC_ID . "=$asAccCust." . EAcc::A_ID;
        $cond = "($condDeb) OR ($condCred)";
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_account as accOther */
        $tbl = $this->resource->getTableName(EAcc::ENTITY_NAME);
        $as = $asAccOther;
        $cols = [];
        $byDebit = "$as." . EAcc::A_ID . "=$asTrans." . ETran::A_DEBIT_ACC_ID;
        $byCredit = "$as." . EAcc::A_ID . "=$asTrans." . ETran::A_CREDIT_ACC_ID;
        $byNotCust = "$as." . EAcc::A_CUST_ID . "!=$asAccCust." . EAcc::A_CUST_ID;
        $cond = "(($byDebit) OR ($byCredit)) AND ($byNotCust)";
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_dwnl_customer as dwnlOther */
        $tbl = $this->resource->getTableName(EDwnlCust::ENTITY_NAME);
        $as = $asDwnlOther;
        $cols = [
            self::A_OTHER_CUST => EDwnlCust::A_MLM_ID
        ];
        $cond = "$as." . EDwnlCust::A_CUSTOMER_ID . "=$asAccOther." . EAcc::A_CUST_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_operation */
        $tbl = $this->resource->getTableName(EOper::ENTITY_NAME);
        $as = $asOper;
        $cols = [];
        $cond = "$as." . EOper::A_ID . "=$asTrans." . ETran::A_OPERATION_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_type_operation */
        $tbl = $this->resource->getTableName(ETypeOper::ENTITY_NAME);
        $as = $asOperType;
        $cols = [
            self::A_TYPE => ETypeOper::A_CODE
        ];
        $cond = "$as." . ETypeOper::A_ID . "=$asOper." . EOper::A_TYPE_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(ETypeAsset::ENTITY_NAME);
        $as = $asAssType;
        $cols = [
            self::A_ASSET => ETypeAsset::A_CODE,
            self::A_ASSET_CUR => ETypeAsset::A_CURRENCY
        ];
        $cond = "$as." . ETypeAsset::A_ID . "=$asAccCust." . EAcc::A_ASSET_TYPE_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);


        /**
         * Query tuning.
         */
        /* WHERE */
        $result->where($asDwnlCust . '.' . EDwnlCust::A_CUSTOMER_ID . '=:' . self::BND_CUST_ID);
        $result->where($asTrans . '.' . ETran::A_DATE_APPLIED . '>:' . self::BND_DATE_FROM);
        $result->where($asTrans . '.' . ETran::A_DATE_APPLIED . '<:' . self::BND_DATE_TO);

        /* ORDER */
        $result->order(self::A_ITEM_ID);

        return $result;
    }

}