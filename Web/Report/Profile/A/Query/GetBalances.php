<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Profile\A\Query;

use Praxigento\Accounting\Repo\Data\Account as EAccount;
use Praxigento\Accounting\Repo\Data\Type\Asset as ETypeAsset;

/**
 * Get current balances.
 */
class GetBalances
    extends \Praxigento\Core\App\Repo\Query\Builder
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_ACC = 'a';
    const AS_TYPE = 't';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_ASSET = 'asset';
    const A_ASSET_CURR = 'assetCurr';
    const A_BALANCE = 'balance';

    /** Bound variables names ('camelCase' naming) */
    const BND_CUST_ID = 'custId';

    /** Entities are used in the query */
    const E_ACC = EAccount::ENTITY_NAME;
    const E_TYPE = ETypeAsset::ENTITY_NAME;

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        $result = $this->conn->select();
        /* define tables aliases for internal usage (in this method) */
        $asAcc = self::AS_ACC;
        $asType = self::AS_TYPE;

        /* FROM prxgt_acc_account */
        $tbl = $this->resource->getTableName(self::E_ACC);
        $as = $asAcc;
        $cols = [
            self::A_BALANCE => EAccount::A_BALANCE
        ];
        $result->from([$as => $tbl], $cols);

        /* JOIN prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(self::E_TYPE);
        $as = $asType;
        $cols = [
            self::A_ASSET => ETypeAsset::A_CODE,
            self::A_ASSET_CURR => ETypeAsset::A_CURRENCY
        ];
        $cond = "$as." . ETypeAsset::A_ID . '=' . $asAcc . '.' . EAccount::A_ASSET_TYPE_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* add filters */
        $byCust = "$asAcc." . EAccount::A_CUST_ID . "=:" . self::BND_CUST_ID;
        $result->where($byCust);

        return $result;
    }
}