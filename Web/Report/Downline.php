<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder as QBLastCalc;
use Praxigento\BonusHybrid\Repo\Data\Downline as EDownline;
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
    /** @var \Praxigento\BonusHybrid\Repo\Dao\Downline */
    private $daoBonDwnl;
    /** @var \Praxigento\Downline\Api\Helper\Config */
    private $hlpCfgDwnl;
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
        \Praxigento\BonusHybrid\Repo\Dao\Downline $daoBonDwnl,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder $qbLastCalc,
        \Praxigento\Dcp\Web\Report\Downline\A\Query $qbDownline,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Api\Helper\Map $hlpDcpMap,
        \Praxigento\Downline\Api\Helper\Config $hlpCfgDwnl
    ) {
        /* don't pass query builder to the parent - we have 4 builders in the operation, not one */
        $this->authenticator = $authenticator;
        $this->procQuery = $procQuery;
        $this->daoBonDwnl = $daoBonDwnl;
        $this->qbDownline = $qbDownline;
        $this->qbLastCalc = $qbLastCalc;
        $this->hlpPeriod = $hlpPeriod;
        $this->hlpDcpMap = $hlpDcpMap;
        $this->hlpCfgDwnl = $hlpCfgDwnl;
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
            list($path, $depth) = $this->getPath($custId, $calcId);
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
     * Define root customer & path to the root customer for the given calculation (plain or compressed).
     *
     * @param int $custId
     * @param int $calcId
     * @return array
     */
    private function getPath($custId, $calcId)
    {
        $byCalcId = EDownline::A_CALC_REF . '=' . (int)$calcId;
        $byCustId = EDownline::A_CUST_REF . '=' . (int)$custId;
        $where = "($byCalcId) AND ($byCustId)";
        $found = $this->daoBonDwnl->get($where);
        if ($found) {
            /* one only item should be found */
            $customerRoot = reset($found);
            $path = $customerRoot->getPath();
            $depth = $customerRoot->getDepth();
        } else {
            /* customer can be omitted in compressed tree */
            $path = $depth = null;
        }

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
        /* MOBI-1600: find min. depth in selection to prevent negative depth in compressed tree */
        $depthMin = $rootDepth;
        /* MILC-79: registry all parents to prevent "holes" in tree structure */
        $regParents = [];
        foreach ($downline as $one) {
            $depth = $one[QBDownline::A_DEPTH];
            $parentId = $one[QBDownline::A_PARENT_REF];
            if ($depth < $depthMin) {
                $depthMin = $depth;
            }
            /* add parent to processed parents registry */
            if (!in_array($parentId, $regParents)) {
                $regParents[] = $parentId;
            }
        }
        /* delete tree leaves that are not distributors */
        $groupDistrs = $this->hlpCfgDwnl->getDowngradeGroupsDistrs();
        foreach ($downline as $one) {
            $custId = $one[QBDownline::A_CUSTOMER_REF];
            $groupId = $one[QBDownline::A_GROUP_ID];
            if (
                !in_array($groupId, $groupDistrs) &&
                !in_array($custId, $regParents)
            ) {
                continue; // skip all not-distrs that are tree leaves
            }
            unset($one[QBDownline::A_GROUP_ID]); // remove extra attribute from result set
            $one[QBDownline::A_PARENT_REF] = ($custId == $rootId) ? $custId : $one[QBDownline::A_PARENT_REF];
            /* shrink path */
            $path = $one[QBDownline::A_PATH];
            if ($rootPath != Cfg::DTPS) {
                $one[QBDownline::A_PATH] = str_replace($rootPath, '', $path);
            }
            /* decrease depth */
            $one[QBDownline::A_DEPTH] = $one[QBDownline::A_DEPTH] - $depthMin;
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
        if ($type != self::REPORT_TYPE_COMPRESSED) {
            $type = self::REPORT_TYPE_COMPLETE;
        }
        return $type;
    }
}