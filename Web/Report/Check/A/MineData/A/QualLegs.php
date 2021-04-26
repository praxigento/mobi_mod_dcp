<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\BonusHybrid\Repo\Data\Compression\Phase2\Legs as ELegs;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Customer as DCustomer;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs as DQualLegs;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs\Item as DItem;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs\Qualification as DQual;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\QualLegs\A\Query as QBGetItems;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs as HGetCalcs;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\IsSchemeEu as HIsSchemeEu;

/**
 * Action to build "QualificationLegs" section of the DCP's "Check" report.
 */
class QualLegs {
    /** @var \Praxigento\BonusHybrid\Repo\Dao\Compression\Phase2\Legs */
    private $daoLegs;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $hlpGetCalcs;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\QualLegs\A\Query */
    private $qbGetItems;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\BonusHybrid\Repo\Dao\Compression\Phase2\Legs $daoLegs,
        QBGetItems $qbGetItems,
        HGetCalcs $hlpGetCalcs,
        HIsSchemeEu $hlpIsSchemeEu
    ) {
        $this->hlpPeriod = $hlpPeriod;
        $this->daoLegs = $daoLegs;
        $this->qbGetItems = $qbGetItems;
        $this->hlpGetCalcs = $hlpGetCalcs;
        $this->hlpIsSchemeEu = $hlpIsSchemeEu;
    }

    /**
     * @param DItem[] $items
     * @param integer $rootLevel
     * @return DItem[]
     * @see \Praxigento\Dcp\Web\Report\Downline::prepareCompressed
     *
     */
    private function compressLegs($items, $rootLevel) {
        $result = [];
        // leave only 3 legs of the first level
        $root = $leg1Item = $leg2Item = null;
        $leg3List = [];
        $teamLevel = $rootLevel + 1;
        foreach ($items as $one) {
            $level = $one->getCustomer()->getLevel();
            if ($level == $teamLevel) {
                $ov = $one->getVolume();
                // check 1st leg
                if (!$leg1Item || ($ov > $leg1Item->getVolume())) {
                    // we need to place current item into 1st leg
                    if ($leg2Item) $leg3List[] = $leg2Item; // transfer 2nd leg item into summarized 3rd leg list
                    if ($leg1Item) $leg2Item = $leg1Item; // transfer former 1st leg to the 2nd leg
                    $leg1Item = $one; // place item to the 1st leg
                } elseif (!$leg2Item || ($ov > $leg2Item->getVolume())) { // check 2nd leg
                    if ($leg2Item) $leg3List[] = $leg2Item; // we need to place current item into 2nd leg
                    $leg2Item = $one;
                } else {
                    $leg3List[] = $one;
                }
            } else {
                $t = 6;
            }

        }
        if ($leg1Item) $result[] = $leg1Item;
        if ($leg2Item) $result[] = $leg2Item;
        if (count($leg3List)) {
            $summary = new DItem();
            $cust = new DCustomer();
            $cust->setId(0);
            $cust->setLevel($rootLevel + 1);
            $cust->setMlmId('N/A');
            $cust->setName('Compressed Leg');
            $summary->setCustomer($cust);
            usort($leg3List, function ($a, $b) {
                return $b->getVolume() - $a->getVolume(); // descending order
            });
            $sumOv = 0;
            foreach ($leg3List as $one) {
                $sumOv += $one->getVolume();
            }
            $summary->setVolume($sumOv);
            $result[] = $summary;
            $result = array_merge($result, $leg3List);
        }
        return $result;
    }

    /**
     * @param $custId
     * @param $period
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\QualLegs|null
     * @throws \Exception
     */
    public function exec($custId, $period) {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $items = [];
        $qual = new DQual();

        /* perform processing */
        $calcs = $this->hlpGetCalcs->exec($dsBegin, $dsEnd);
        if (count($calcs) > 0) {
            $calcId = $calcs[Cfg::CODE_TYPE_CALC_COMPRESS_PHASE1] ?? null;
            if ($calcId) {
                [$rootLevel, $items] = $this->getItems($calcId, $custId);
                $items = $this->compressLegs($items, $rootLevel);
                $qual = $this->getQualData($calcId, $custId);
                $qual->setRootLevel($rootLevel);
            }
        }

        /* compose result */
        $result = new DQualLegs();
        $result->setItems($items);
        $result->setQualification($qual);
        return $result;
    }

    /**
     * @param int $calcId
     * @param int $custId
     * @return array
     * @throws \Exception
     */
    private function getItems($calcId, $custId) {
        $query = $this->qbGetItems->build();
        $conn = $query->getConnection();
        $bind = [
            QBGetItems::BND_CALC_ID => $calcId,
            QBGetItems::BND_CUST_ID => $custId
        ];
        $rs = $conn->fetchAll($query, $bind);

        $items = [];
        $rootLevel = 0;
        foreach ($rs as $one) {
            /* get DB data */
            $custIdDb = $one[QBGetItems::A_CUST_ID];
            if ($custIdDb == $custId) {
                /* get root level for customer */
                $rootLevel = $depth = $one[QBGetItems::A_DEPTH];
            } else {
                /* compose children items */
                $depth = $one[QBGetItems::A_DEPTH];
                $mlmId = $one[QBGetItems::A_MLM_ID];
                $nameFirst = trim($one[QBGetItems::A_NAME_FIRST]);
                $nameLast = trim($one[QBGetItems::A_NAME_LAST]);
                $ov = $one[QBGetItems::A_OV];

                /* composite values */
                $name = "$nameFirst $nameLast";

                /* compose API data */
                $customer = new DCustomer();
                $customer->setId($custIdDb);
                $customer->setMlmId($mlmId);
                $customer->setName($name);
                $customer->setLevel($depth);
                $item = new DItem();
                $item->setCustomer($customer);
                $item->setVolume($ov);

                $items[] = $item;
            }
        }
        return [$rootLevel, $items];
    }

    private function getQualData($calcId, $custId) {
        $ids = [
            ELegs::A_CALC_REF => $calcId,
            ELegs::A_CUST_REF => $custId
        ];
        /** @var ELegs $entity */
        $entity = $this->daoLegs->getById($ids);
        if ($entity) {
            $maxLegCust = $entity->getCustMaxRef();
            $maxLegOv = $entity->getLegMax();
            $maxLegQual = $entity->getPvQualMax();
            $secondLegCust = $entity->getCustSecondRef();
            $secondLegOv = $entity->getLegSecond();
            $secondLegQual = $entity->getPvQualSecond();
            $otherLegsOv = $entity->getLegOthers();
            $otherLegsQual = $entity->getPvQualOther();
        } else {
            $maxLegCust = 0;
            $maxLegOv = 0;
            $maxLegQual = 0;
            $secondLegCust = 0;
            $secondLegOv = 0;
            $secondLegQual = 0;
            $otherLegsOv = 0;
            $otherLegsQual = 0;
        }


        /* compose result */
        $result = new DQual();
        $result->setMaxLegCustId($maxLegCust);
        $result->setMaxLegOv($maxLegOv);
        $result->setMaxLegQual($maxLegQual);
        $result->setSecondLegCust($secondLegCust);
        $result->setSecondLegOv($secondLegOv);
        $result->setSecondLegQual($secondLegQual);
        $result->setOtherLegsOv($otherLegsOv);
        $result->setOtherLegsQual($otherLegsQual);
        return $result;
    }

}
