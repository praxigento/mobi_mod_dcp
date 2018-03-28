<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\Core\Api\Helper\Period as HPeriod;
use Praxigento\Dcp\Api\Web\Report\Accounting\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Accounting\Response as AResponse;
use Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data as AData;
use Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Balance as DRespBalance;
use Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer as DRespCust;
use Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans as DRespTrans;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Accounting\A\Query\Trans as QBAccTrans;
use Praxigento\Dcp\Web\Report\Accounting\A\Query\Balance as QBBal;
use Praxigento\Downline\Repo\Query\Customer\Get as QBCust;

class Accounting
    implements \Praxigento\Dcp\Api\Web\Report\AccountingInterface
{
    /** @var \Praxigento\Core\Api\App\Web\Authenticator */
    private $authenticator;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Core\App\Web\Processor\WithQuery\Conditions */
    private $procQuery;
    /** @var \Praxigento\Dcp\Web\Report\Accounting\A\Query\Balance */
    private $qbBalance;
    /** @var \Praxigento\Downline\Repo\Query\Customer\Get */
    private $qbCust;
    /** @var \Praxigento\Dcp\Web\Report\Accounting\A\Query\Trans */
    private $qbDcpTrans;

    public function __construct(
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\Core\App\Web\Processor\WithQuery\Conditions $procQuery,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Web\Report\Accounting\A\Query\Trans $qbDcpTrans,
        \Praxigento\Dcp\Web\Report\Accounting\A\Query\Balance $qbBalance,
        \Praxigento\Downline\Repo\Query\Customer\Get $qbCust
    ) {
        $this->authenticator = $authenticator;
        $this->procQuery = $procQuery;
        $this->hlpPeriod = $hlpPeriod;
        $this->qbDcpTrans = $qbDcpTrans;
        $this->qbBalance = $qbBalance;
        $this->qbCust = $qbCust;
    }

    public function exec($request)
    {
        assert($request instanceof ARequest);
        /** define local working data */
        $reqData = $request->getData();
        $period = $reqData->getPeriod();
        $cond = $request->getConditions();

        $result = new AResponse();
        $respRes = $result->getResult();

        /** perform processing */
        $custId = $this->authenticator->getCurrentUserId($request);
        if (!$period) {
            $period = $this->hlpPeriod->getPeriodCurrent(null, 0, HPeriod::TYPE_MONTH);
        }
        if ($custId) {
            /* get nested composite parts */
            list($balOpen, $balClose) = $this->getBalances($custId, $period);
            $customer = $this->getCustomer($custId);
            $trans = $this->getTransactions($custId, $period, $cond);

            /* compose API object */
            $data = new AData();
            $data->setBalanceOpen($balOpen);
            $data->setBalanceClose($balClose);
            $data->setCustomer($customer);
            $data->setTrans($trans);
            $result->setData($data);

            $respRes->setCode(AResponse::CODE_SUCCESS);
        } else {
            $respRes->setCode(AResponse::CODE_NO_DATA);
        }

        /** compose result */
        return $result;
    }

    /**
     * Get balances for given customer for given period.
     *
     * @param $custId
     * @param $period
     * @return array [$balOpen, $balClose]
     */
    private function getBalances($custId, $period)
    {
        /* dates for balances */
        $dateFirst = $this->hlpPeriod->getPeriodFirstDate($period, HPeriod::TYPE_MONTH);
        $dsOpen = $this->hlpPeriod->getPeriodPrev($dateFirst);
        $dsClose = $this->hlpPeriod->getPeriodLastDate($period, HPeriod::TYPE_MONTH);

        /** @var \Magento\Framework\DB\Select $queryBal */
        $queryBal = $this->qbBalance->build();
        $bindBal = [
            QBBal::BIND_MAX_DATE => $dsOpen,
            QBBal::BND_CUST_ID => $custId
        ];
        $balOpen = $this->queryBalances($queryBal, $bindBal);
        $bindBal [QBBal::BIND_MAX_DATE] = $dsClose;
        $balClose = $this->queryBalances($queryBal, $bindBal);

        return [$balOpen, $balClose];
    }

    /**
     * @param $custId
     * @return \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Customer
     * @throws \Exception
     */
    private function getCustomer($custId)
    {
        /** @var \Magento\Framework\DB\Select $query */
        $query = $this->qbCust->build();
        $bind = [
            QBCust::BND_CUST_ID => $custId
        ];
        /* perform query and collect data */
        $conn = $query->getConnection();
        $rs = $conn->fetchRow($query, $bind);
        $id = $rs[QBCust::A_ID];
        $mlmId = $rs[QBCust::A_MLM_ID];
        $nameFirst = $rs[QBCust::A_NAME_FIRST];
        $nameLast = $rs[QBCust::A_NAME_LAST];

        /** compose result */
        $result = new DRespCust();
        $result->setId($id);
        $result->setMlmId($mlmId);
        $result->setNameFirst($nameFirst);
        $result->setNameLast($nameLast);
        return $result;
    }

    /**
     * @param int $custId
     * @param string $period YYYYMM or YYYYMMDD
     * @param \Praxigento\Core\Api\App\Web\Request\Conditions $cond
     * @return \Praxigento\Dcp\Api\Web\Report\Accounting\Response\Data\Trans[]
     */
    private function getTransactions($custId, $period, $cond)
    {
        $result = [];
        $query = $this->qbDcpTrans->build();

        /* apply dates for transactions */
        $dateFrom = $this->hlpPeriod->getTimestampFrom($period, HPeriod::TYPE_MONTH);
        $dateTo = $this->hlpPeriod->getTimestampTo($period, HPeriod::TYPE_MONTH);
        $bind = [
            QBAccTrans::BND_CUST_ID => $custId,
            QBAccTrans::BND_DATE_FROM => $dateFrom,
            QBAccTrans::BND_DATE_TO => $dateTo
        ];

        /* TODO: use regular arguments, don't use context here */
        $ctx = new \Praxigento\Core\App\Web\Processor\WithQuery\Conditions\Context();
        $ctx->setQuery($query);
        $ctx->setConditions($cond);
        $this->procQuery->exec($ctx);
        $query = $ctx->getQuery();
        $conn = $query->getConnection();
        $rs = $conn->fetchAll($query, $bind);
        foreach ($rs as $tran) {
            /* parse query entry */
            $accOwn = $tran[QBAccTrans::A_ACC_OWN];
            $accDebit = $tran[QBAccTrans::A_ACC_DEBIT];
            $asset = $tran[QBAccTrans::A_ASSET];
            $date = $tran[QBAccTrans::A_DATE];
            $details = $tran[QBAccTrans::A_DETAILS];
            $itemId = $tran[QBAccTrans::A_ITEM_ID];
            $otherCustId = $tran[QBAccTrans::A_OTHER_CUST];
            $type = $tran[QBAccTrans::A_TYPE];
            $value = $tran[QBAccTrans::A_VALUE];

            /* pre-process data */
            if ($accOwn == $accDebit) $value = -$value;
            if (is_null($otherCustId)) $otherCustId = Cfg::CUST_SYS_NAME;

            /* compose API entry */
            $item = new DRespTrans();
            $item->setAsset($asset);
            $item->setCustomerId($otherCustId);
            $item->setDate($date);
            $item->setDetails($details);
            $item->setTransId($itemId);
            $item->setType($type);
            $item->setValue($value);
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Perform 'get balance' query (for open/close balance) and compose API compatible result.
     *
     * @param \Magento\Framework\DB\Select $query
     * @param $bind
     * @return DRespBalance[]
     */
    private function queryBalances(\Magento\Framework\DB\Select $query, $bind) {
        $result = [];
        $conn = $query->getConnection();
        $rs = $conn->fetchAll($query, $bind);
        foreach ($rs as $one) {
            $asset = $one[QBBal::A_ASSET];
            $value = $one[QBBal::A_BALANCE];
            $item = new DRespBalance();
            $item->setAsset($asset);
            $item->setValue($value);
            $result[] = $item;
        }
        return $result;
    }

}