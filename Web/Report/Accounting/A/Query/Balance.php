<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Accounting\A\Query;

use Praxigento\Accounting\Repo\Data\Account as EAccount;
use Praxigento\Accounting\Repo\Data\Type\Asset as ETypeAsset;

class Balance
    extends \Praxigento\Accounting\Repo\Query\Balance\OnDate\Closing
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_ASSET_TYPE = 'assType';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_ASSET = 'asset';
    const A_CURRENCY = 'currency';

    /** Bound variables names ('camelCase' naming) */
    const BND_CUST_ID = 'custId'; // to get asset balances

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        $result = parent::build();
        /* define tables aliases for internal usage (in this method) */
        $asType = self::AS_ASSET_TYPE;
        $asAcc = self::AS_ACC;

        /* JOIN prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(ETypeAsset::ENTITY_NAME);
        $as = $asType;
        $cols = [
            self::A_ASSET => ETypeAsset::A_CODE,
            self::A_CURRENCY => ETypeAsset::A_CURRENCY
        ];
        $cond = $as . '.' . ETypeAsset::A_ID . '=' . $asAcc . '.' . EAccount::A_ASSET_TYPE_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* add filter by customer */
        $where = $asAcc . '.' . EAccount::A_CUST_ID . '=:' . self::BND_CUST_ID;
        $result->where($where);

        return $result;
    }
}