<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Accounting\Response;

/**
 * Accessors use 'CamelCase' naming strategy (data object default), but data inside use 'snake_case' naming strategy
 * (API default). Repo queries should use 'snake_case' namings to prepare array data, DataObject will return
 * 'snake_case' property if 'CamelCase' will not be found.
 */
class Data
    extends \Praxigento\Core\Data
{
    const A_BAL_CLOSE = 'balance_close';
    const A_BAL_OPEN = 'balance_open';
    const A_CURRENCY = 'currency';
    const A_CUSTOMER = 'customer';
    const A_TRANS = 'trans';

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[]
     */
    public function getBalanceClose()
    {
        $result = parent::get(self::A_BAL_CLOSE);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[]
     */
    public function getBalanceOpen()
    {
        $result = parent::get(self::A_BAL_OPEN);
        return $result;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        $result = parent::get(self::A_CURRENCY);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer
     */
    public function getCustomer()
    {
        $result = parent::get(self::A_CUSTOMER);
        return $result;
    }

    /**
     * @return \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans[]
     */
    public function getTrans()
    {
        $result = parent::get(self::A_TRANS);
        return $result;
    }


    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[] $data
     * @return void
     */
    public function setBalanceClose($data)
    {
        parent::set(self::A_BAL_CLOSE, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance[] $data
     * @return void
     */
    public function setBalanceOpen($data)
    {
        parent::set(self::A_BAL_OPEN, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setCurrency($data)
    {
        parent::set(self::A_CURRENCY, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer $data
     * @return void
     */
    public function setCustomer($data)
    {
        parent::set(self::A_CUSTOMER, $data);
    }

    /**
     * @param \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans[] $data
     * @return void
     */
    public function setTrans($data)
    {
        parent::set(self::A_TRANS, $data);
    }
}