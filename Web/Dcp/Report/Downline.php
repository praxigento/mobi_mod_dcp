<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report;

use Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder as QBLastCalc;
use Praxigento\Dcp\Api\Web\Dcp\Report\Downline\Request as ARequest;
use Praxigento\Dcp\Api\Web\Dcp\Report\Downline\Response as AResponse;
use Praxigento\Santegra\Config as Cfg;
use Praxigento\Santegra\Repo\Own\Query\Report\Downline as QBDownline;

class Downline
    extends \Praxigento\Core\App\Web\Processor\WithQuery
    implements \Praxigento\Dcp\Api\Web\Dcp\Report\DownlineInterface
{
    /**
     * Types of the requested report.
     */
    const REPORT_TYPE_COMPLETE = 'complete';
    const REPORT_TYPE_COMPRESSED = 'compressed';

    /**
     * Name of the local context variables.
     */
    const VAR_CALC_ID = 'calcId';
    const VAR_CUST_DEPTH = 'depth';
    const VAR_CUST_ID = 'custId';
    const VAR_CUST_PATH = 'path';

    /** @var \Praxigento\Core\Api\App\Web\Authenticator */
    private $authenticator;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Santegra\Repo\Own\Query\Report\Downline */
    private $qbDownline;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder */
    private $qbLastCalc;
    /** @var \Praxigento\Downline\Repo\Entity\Customer */
    private $repoDwnlCust;
    /** @var \Praxigento\Downline\Repo\Entity\Snap */
    private $repoSnap;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\Downline\Repo\Entity\Customer $repoDwnlCust,
        \Praxigento\Downline\Repo\Entity\Snap $repoSnap,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder $qbLastCalc,
        \Praxigento\Santegra\Repo\Own\Query\Report\Downline $qbDownline,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod
    ) {
        /* don't pass query builder to the parent - we have 4 builders in the operation, not one */
        parent::__construct($manObj, null);
        $this->authenticator = $authenticator;
        $this->repoDwnlCust = $repoDwnlCust;
        $this->repoSnap = $repoSnap;
        $this->qbDownline = $qbDownline;
        $this->qbLastCalc = $qbLastCalc;
        $this->hlpPeriod = $hlpPeriod;
    }

    protected function authorize(\Praxigento\Core\Data $ctx)
    {
        /* do nothing - in Production Mode current customer's ID is used as root customer ID */
    }

    protected function createQuerySelect(\Praxigento\Core\Data $ctx)
    {
        $query = $this->qbDownline->build();
        $ctx->set(self::CTX_QUERY, $query);
    }

    public function exec($request)
    {
        assert($request instanceof ARequest);
        $data = parent::process($request);
        $result = new AResponse($data);
        /* conveyors are bad for debug */
        if ($data->getCustomer()) {
            $result->getResult()->setCode(AResponse::CODE_SUCCESS);
        } else {
            $result->getResult()->setCode(AResponse::CODE_NO_DATA);
        }
        return $result;
    }

    /**
     * Get complete calculation ID for given date by calculation type code.
     *
     * @param $calcTypeCode
     * @param $dateEnd
     * @return mixed
     */
    private function getCalcId($calcTypeCode, $dateEnd)
    {
        $query = $this->qbLastCalc->build();
        $bind = [
            QBLastCalc::BND_CODE => $calcTypeCode,
            QBLastCalc::BND_DATE => $dateEnd,
            QBLastCalc::BND_STATE => Cfg::CALC_STATE_COMPLETE
        ];

        /* fetch & parse data */
        $conn = $query->getConnection();
        $rs = $conn->fetchRow($query, $bind);
        $result = $rs[QBLastCalc::A_CALC_ID];
        return $result;
    }

    protected function performQuery(\Praxigento\Core\Data $ctx)
    {
        parent::performQuery($ctx);
        /* MOBI-1033 */
        $vars = $ctx->get(self::CTX_VARS);
        $raw = $ctx->get(self::CTX_RESULT);
        $rootId = $vars->get(self::VAR_CUST_ID);
        $rootPath = $vars->get(self::VAR_CUST_PATH);
        $rootDepth = $vars->get(self::VAR_CUST_DEPTH);
        $result = [];
        foreach ($raw as $item) {
            $custId = $item[QBDownline::A_CUSTOMER_REF];
            $path = $item[QBDownline::A_PATH];
            if ($custId == $rootId) {
                /* set parent ID for the root */
                $item[QBDownline::A_PARENT_REF] = $custId;
            }
            /* shrink path */
            $shrinked = str_replace($rootPath, '', $path);
            $item[QBDownline::A_PATH] = $shrinked;
            /* decrease depth */
            $decreased = $item[QBDownline::A_DEPTH] - $rootDepth;
            $item[QBDownline::A_DEPTH] = $decreased;
            /* place into result set */
            $result[] = $item;
        }
        $ctx->set(self::CTX_RESULT, $result);
    }

    protected function populateQuery(\Praxigento\Core\Data $ctx)
    {
        /* get working vars from context */
        /** @var \Praxigento\Core\Data $bind */
        $bind = $ctx->get(self::CTX_BIND);
        /** @var \Praxigento\Core\Data $vars */
        $vars = $ctx->get(self::CTX_VARS);

        /* get working vars */
        $rootCustId = $vars->get(self::VAR_CUST_ID);
        $rootPath = $vars->get(self::VAR_CUST_PATH);
        $calcRef = $vars->get(self::VAR_CALC_ID);
        $path = $rootPath . $rootCustId . Cfg::DTPS . '%';

        /* bind values for query parameters */
        $bind->set(QBDownline::BND_CALC_ID, $calcRef);
        $bind->set(QBDownline::BND_PATH, $path);
        $bind->set(QBDownline::BND_CUST_ID, $rootCustId);
    }

    protected function prepareQueryParameters(\Praxigento\Core\Data $ctx)
    {
        /* get working vars from context */
        /** @var \Praxigento\Core\Data $vars */
        $vars = $ctx->get(self::CTX_VARS);
        /** @var \Praxigento\Dcp\Api\Web\Dcp\Report\Downline\Request $req */
        $req = $ctx->get(self::CTX_REQ);
        /** @var \Praxigento\Dcp\Api\Web\Dcp\Report\Downline\Request\Data $req */
        $reqData = $req->getData();
        /** @var \Praxigento\Core\Api\App\Web\Request\Dev $reqDev */
        $reqDev = $req->getDev();
        /* extract HTTP request parameters */
        $period = $reqData->getPeriod();
        $reqType = $reqData->getType();
        $devCustId = $reqDev->getCustId();

        /**
         * Define period.
         */
        if (!$period) {
            /* CAUTION: this code will be failed after 2999 year. Please, call to the author in this case. */
            $period = '2999';
        }
        $period = $this->hlpPeriod->getPeriodLastDate($period);

        /**
         * Define root customer & path to the root customer on the date.
         */
        $request = new \Praxigento\Core\Api\App\Web\Request();
        $dev = new \Praxigento\Core\Api\App\Web\Request\Dev();
        $dev->setCustId($devCustId);
        $request->setDev($dev);
        $rootCustId = $this->authenticator->getCurrentUserId($request);

        /** @var \Praxigento\Downline\Repo\Entity\Data\Snap $customerRoot */
        $customerRoot = $this->repoSnap->getByCustomerIdOnDate($rootCustId, $period);
        if ($customerRoot === false) {
            /* probably this is new customer that is not in Downline Snaps */
            $customerRoot = $this->repoDwnlCust->getById($rootCustId);
        }
        $path = $customerRoot->getPath();
        $depth = $customerRoot->getDepth();

        /**
         * Define calculation ID to get downline data.
         */
        $calcTypeCode = null;
        $onDate = $this->hlpPeriod->getPeriodLastDate($period);
        $current = $this->hlpPeriod->getPeriodCurrent();
        if ($onDate >= $current) {
            /* use forecast downlines */
            $calcTypeCode = Cfg::CODE_TYPE_CALC_FORECAST_PLAIN;
            if ($reqType == self::REPORT_TYPE_COMPRESSED) {
                $calcTypeCode = Cfg::CODE_TYPE_CALC_FORECAST_PHASE1;
            }
        } else {
            /* use historical downlines */
            $calcTypeCode = Cfg::CODE_TYPE_CALC_PV_WRITE_OFF;
            if ($reqType == self::REPORT_TYPE_COMPRESSED) {
                $calcTypeCode = Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1;
            }
        }
        $calcId = $this->getCalcId($calcTypeCode, $onDate);

        /* save working variables into execution context */
        $vars->set(self::VAR_CALC_ID, $calcId);
        $vars->set(self::VAR_CUST_DEPTH, $depth);
        $vars->set(self::VAR_CUST_ID, $rootCustId);
        $vars->set(self::VAR_CUST_PATH, $path);
    }
}