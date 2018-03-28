<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Accounting\Response;

/**
 * Accessors use 'CamelCase' naming strategy (data object default), but data inside use 'snake_case' naming strategy
 * (API default). Repo queries should use 'snake_case' namings to prepare array data, DataObject will return
 * 'snake_case' property if 'CamelCase' will not be found.
 *
 * @method \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[] getBalanceClose()
 * @method void setBalanceClose(\Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[] $data)
 * @method \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[] getBalanceOpen()
 * @method void setBalanceOpen(\Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[] $data)
 * @method string getCurrency()
 * @method void setCurrency(string $data)
 * @method \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer getCustomer()
 * @method void setCustomer(\Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer $data)
 * @method \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans[] getTrans()
 * @method void setTrans(\Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans[] $data)
 */
class Data
    extends \Praxigento\Core\Data
{
    const A_BAL_CLOSE = 'balance_close';
    const A_BAL_OPEN = 'balance_open';
    const A_CURRENCY = 'currency';
    const A_CUSTOMER = 'customer';
    const A_TRANS = 'trans';

}