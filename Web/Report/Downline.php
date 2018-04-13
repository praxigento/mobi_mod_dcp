<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder as QBLastCalc;
use Praxigento\Dcp\Api\Web\Report\Downline\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Downline\Response as AResponse;
use Praxigento\Dcp\Api\Web\Report\Downline\Response\Entry as ARespEntry;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Downline\A\Query as QBDownline;

class Downline
    implements \Praxigento\Dcp\Api\Web\Report\DownlineInterface
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
    /** @var \Praxigento\Downline\Repo\Dao\Customer */
    private $daoDwnlCust;
    /** @var \Praxigento\Downline\Repo\Dao\Snap */
    private $daoSnap;
    /** @var \Praxigento\Dcp\Api\Helper\Map */
    private $hlpDcpMap;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Report\Downline\A\Query */
    private $qbDownline;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder */
    private $qbLastCalc;

    public function __construct(
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\Downline\Repo\Dao\Customer $daoDwnlCust,
        \Praxigento\Downline\Repo\Dao\Snap $daoSnap,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder $qbLastCalc,
        \Praxigento\Dcp\Web\Report\Downline\A\Query $qbDownline,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Api\Helper\Map $hlpDcpMap
    ) {
        /* don't pass query builder to the parent - we have 4 builders in the operation, not one */
        $this->authenticator = $authenticator;
        $this->daoDwnlCust = $daoDwnlCust;
        $this->daoSnap = $daoSnap;
        $this->qbDownline = $qbDownline;
        $this->qbLastCalc = $qbLastCalc;
        $this->hlpPeriod = $hlpPeriod;
        $this->hlpDcpMap = $hlpDcpMap;
    }

    public function exec($request)
    {
        assert($request instanceof ARequest);
        /** define local working data */
        $reqData = $request->getData();
        $period = $reqData->getPeriod();
        $type = $reqData->getType();
        $cond = $request->getConditions();

        $result = new AResponse();
        $respRes = $result->getResult();

        /** perform processing */
        $custId = $this->authenticator->getCurrentUserId($request);
        if ($custId) {
            $period = $this->validatePeriod($period);
            $type = $this->validateReportType($type);
            $calcId = $this->getCalculationId($period, $type);
            list($path, $depth) = $this->getPath($custId, $period);
            $downline = $this->loadDownline($calcId, $custId, $path);
            $respData = $this->prepareDownline($downline, $custId, $path, $depth);

            $result->setData($respData);
            $respRes->setCode(AResponse::CODE_SUCCESS);
        } else {
            $respRes->setCode(AResponse::CODE_NO_DATA);
        }

        /** compose result */
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

    /**
     * Get calculation ID according to given $period & $type to load downline tree.
     *
     * @param string $period 'YYMM'
     * @param string $type
     * @return int
     */
    private function getCalculationId($period, $type)
    {
        $calcTypeCode = null;
        $onDate = $this->hlpPeriod->getPeriodLastDate($period);
        $current = $this->hlpPeriod->getPeriodCurrent();
        if ($onDate >= $current) {
            /* use forecast downlines */
            $calcTypeCode = Cfg::CODE_TYPE_CALC_FORECAST_PLAIN;
            if ($type == self::REPORT_TYPE_COMPRESSED) {
                $calcTypeCode = Cfg::CODE_TYPE_CALC_FORECAST_PHASE1;
            }
        } else {
            /* use historical downlines */
            $calcTypeCode = Cfg::CODE_TYPE_CALC_PV_WRITE_OFF;
            if ($type == self::REPORT_TYPE_COMPRESSED) {
                $calcTypeCode = Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1;
            }
        }
        $result = $this->getCalcId($calcTypeCode, $onDate);
        return $result;
    }

    /**
     * Define root customer & path to the root customer on the date.
     *
     * @param int $custId
     * @param string $period YYYYMMDD
     * @return array
     */
    private function getPath($custId, $period)
    {
        /** @var \Praxigento\Downline\Repo\Data\Snap $customerRoot */
        $customerRoot = $this->daoSnap->getByCustomerIdOnDate($custId, $period);
        if ($customerRoot === false) {
            /* probably this is new customer that is not in Downline Snaps */
            $customerRoot = $this->daoDwnlCust->getById($custId);
        }
        $path = $customerRoot->getPath();
        $depth = $customerRoot->getDepth();
        return [$path, $depth];
    }

    /**
     * Perform query & load downline data from DB.
     *
     * @param int $calcId
     * @param int $custId
     * @param string $path
     * @return array
     */
    private function loadDownline($calcId, $custId, $path)
    {
        $query = $this->qbDownline->build();
        $bind = [
            QBDownline::BND_CALC_ID => $calcId,
            QBDownline::BND_CUST_ID => $custId,
            QBDownline::BND_PATH => $path . '%',
        ];
        $conn = $query->getConnection();
        $rs = $conn->fetchAll($query, $bind);
        return $rs;
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

    private function prepareDownline($downline, $rootId, $rootPath, $rootDepth)
    {
        $result = [];
        foreach ($downline as $one) {
            $custId = $one[QBDownline::A_CUSTOMER_REF];

            $parentId = ($custId == $rootId) ? $custId : $one[QBDownline::A_PARENT_REF];
            $one[QBDownline::A_PARENT_REF] = $parentId;
            /* shrink path */
            $path = $one[QBDownline::A_PATH];
            $path = str_replace($rootPath, '', $path);
            $one[QBDownline::A_PATH] = $path;
            /* decrease depth */
            $depth = $one[QBDownline::A_DEPTH] - $rootDepth;
            $one[QBDownline::A_DEPTH] = $depth;
            /* change rank code to UI value */
            $rankCode = $one[QBDownline::A_RANK_CODE];
            $rankUi = $this->hlpDcpMap->rankCodeToUi($rankCode);
            $one[QBDownline::A_RANK_CODE] = $rankUi;
            /* place into result set */
//            $entry = new ARespEntry($one);
            $result[] = $one;
        }
        return $result;
    }

    protected function prepareQueryParameters(\Praxigento\Core\Data $ctx)
    {
        /* get working vars from context */
        /** @var \Praxigento\Core\Data $vars */
        $vars = $ctx->get(self::CTX_VARS);
        /** @var \Praxigento\Dcp\Api\Web\Report\Downline\Request $req */
        $req = $ctx->get(self::CTX_REQ);
        /** @var \Praxigento\Dcp\Api\Web\Report\Downline\Request\Data $req */
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

        /** @var \Praxigento\Downline\Repo\Data\Snap $customerRoot */
        $customerRoot = $this->daoSnap->getByCustomerIdOnDate($rootCustId, $period);
        if ($customerRoot === false) {
            /* probably this is new customer that is not in Downline Snaps */
            $customerRoot = $this->daoDwnlCust->getById($rootCustId);
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

    /**
     * Validate & normalize given period.
     *
     * @param string $period YYYY, YYYYMM, YYYYMMDD
     * @return string YYYYMMDD
     */
    private function validatePeriod($period)
    {
        if (!$period) {
            $period = $this->hlpPeriod->getPeriodCurrent(null, 0, HPeriod::TYPE_MONTH);
        }
        /* YYYYMMDD => YYYYMM */
        if (strlen($period) > 6) {
            $period = substr($period, 0, 6);
        }
        $result = $this->hlpPeriod->getPeriodLastDate($period);
        return $result;
    }

    /**
     * Set default type 'compressed'.
     *
     * @param string $type
     * @return string
     */
    private function validateReportType($type)
    {
        if ($type != self::REPORT_TYPE_COMPLETE) {
            $type = self::REPORT_TYPE_COMPRESSED;
        }
        return $type;
    }
}