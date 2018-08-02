<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder as QBLastCalc;
use Praxigento\Core\Api\Helper\Period as HPeriod;
use Praxigento\Dcp\Api\Web\Report\Downline\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Downline\Response as AResponse;
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
    /** @var \Praxigento\Core\App\Web\Processor\WithQuery\Conditions */
    private $procQuery;
    /** @var \Praxigento\Dcp\Web\Report\Downline\A\Query */
    private $qbDownline;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder */
    private $qbLastCalc;

    public function __construct(
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\Core\App\Web\Processor\WithQuery\Conditions $procQuery,
        \Praxigento\Downline\Repo\Dao\Customer $daoDwnlCust,
        \Praxigento\Downline\Repo\Dao\Snap $daoSnap,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder $qbLastCalc,
        \Praxigento\Dcp\Web\Report\Downline\A\Query $qbDownline,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Api\Helper\Map $hlpDcpMap
    ) {
        /* don't pass query builder to the parent - we have 4 builders in the operation, not one */
        $this->authenticator = $authenticator;
        $this->procQuery = $procQuery;
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
            $downline = $this->loadDownline($calcId, $custId, $path, $cond);
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
        $dstampEnd = $rs[QBLastCalc::A_DS_END];
        if (
            ($dateEnd == $dstampEnd) ||
            ($calcTypeCode == Cfg::CODE_TYPE_CALC_FORECAST_PLAIN) ||
            ($calcTypeCode == Cfg::CODE_TYPE_CALC_FORECAST_PHASE1)
        ) {
            $result = $rs[QBLastCalc::A_CALC_ID];
        } else {
            $result = null;
        }
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

        /* use historical downlines */
        $calcTypeCode = Cfg::CODE_TYPE_CALC_PV_WRITE_OFF;
        if ($type == self::REPORT_TYPE_COMPRESSED) {
            $calcTypeCode = Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1;
        }
        $result = $this->getCalcId($calcTypeCode, $onDate);
        if (!$result) {
            /* use forecast downlines */
            $calcTypeCode = Cfg::CODE_TYPE_CALC_FORECAST_PLAIN;
            if ($type == self::REPORT_TYPE_COMPRESSED) {
                $calcTypeCode = Cfg::CODE_TYPE_CALC_FORECAST_PHASE1;
            }
            $result = $this->getCalcId($calcTypeCode, $onDate);
        }

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
     * @param \Praxigento\Core\Api\App\Web\Request\Conditions $cond
     * @return array
     */
    private function loadDownline($calcId, $custId, $path, $cond)
    {
        $query = $this->qbDownline->build();
        $bndPath = $path . $custId . Cfg::DTPS . '%';
        $bind = [
            QBDownline::BND_CALC_ID => $calcId,
            QBDownline::BND_CUST_ID => $custId,
            QBDownline::BND_PATH => $bndPath,
        ];
        $query = $this->procQuery->exec($query, $cond);
        $conn = $query->getConnection();
        $rs = $conn->fetchAll($query, $bind);
        return $rs;
    }

    private function prepareDownline($downline, $rootId, $rootPath, $rootDepth)
    {
        $result = [];
        foreach ($downline as $one) {
            $custId = $one[QBDownline::A_CUSTOMER_REF];
            $one[QBDownline::A_PARENT_REF] = ($custId == $rootId) ? $custId : $one[QBDownline::A_PARENT_REF];
            /* shrink path */
            $path = $one[QBDownline::A_PATH];
            $one[QBDownline::A_PATH] = str_replace($rootPath, '', $path);
            /* decrease depth */
            $one[QBDownline::A_DEPTH] = $one[QBDownline::A_DEPTH] - $rootDepth;
            /* change rank code to UI value */
            $rankCode = $one[QBDownline::A_RANK_CODE];
            $one[QBDownline::A_RANK_CODE] = $this->hlpDcpMap->rankCodeToUi($rankCode);

            /* place into result set */
            $result[] = $one;
        }
        return $result;
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