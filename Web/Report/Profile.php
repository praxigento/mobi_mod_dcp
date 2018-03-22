<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\Accounting\Repo\Data\Account as EAccount;
use Praxigento\BonusHybrid\Repo\Entity\Data\Downline as EBonDwnl;
use Praxigento\Dcp\Api\Web\Report\Profile\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Profile\Response as AResponse;
use Praxigento\Dcp\Api\Web\Report\Profile\Response\Data as AData;
use Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Balance\Item as DBalanceItem;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Profile\A\Query\GetBalances as QBGetBalances;
use Praxigento\Dcp\Web\Report\Profile\A\Query\GetBonusStats as QBGetBonusStats;

class Profile
    implements \Praxigento\Dcp\Api\Web\Report\ProfileInterface
{
    /** @var \Praxigento\Core\Api\App\Web\Authenticator\Front */
    private $authenticator;
    /** @var \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBalances */
    private $qbGetBalances;
    /** @var \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBonusStats */
    private $qbGetBonusStats;
    /** @var \Praxigento\BonusHybrid\Repo\Entity\Downline */
    private $repoBonDwnl;
    /** @var \Praxigento\Downline\Repo\Entity\Customer */
    private $repoDwnlCust;
    /** @var \Praxigento\PensionFund\Repo\Entity\Registry */
    private $repoPension;
    /** @var \Praxigento\BonusBase\Repo\Entity\Rank */
    private $repoRank;

    public function __construct(
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\BonusBase\Repo\Entity\Rank $repoRank,
        \Praxigento\BonusHybrid\Repo\Entity\Downline $repoBonDwnl,
        \Praxigento\Downline\Repo\Entity\Customer $repoDwnlCust,
        \Praxigento\PensionFund\Repo\Entity\Registry $repoPension,
        \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBalances $qbGetBalances,
        \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBonusStats $qbGetBonusStats
    ) {
        $this->authenticator = $authenticator;
        $this->repoRank = $repoRank;
        $this->repoBonDwnl = $repoBonDwnl;
        $this->repoDwnlCust = $repoDwnlCust;
        $this->repoPension = $repoPension;
        $this->qbGetBalances = $qbGetBalances;
        $this->qbGetBonusStats = $qbGetBonusStats;
    }

    public function exec($request)
    {
        assert($request instanceof ARequest);
        $result = new AResponse();
        $respRes = $result->getResult();

        /** perform processing */
        $custId = $this->authenticator->getCurrentUserId($request);
        if ($custId) {
            /* get downline props (data's root props) */
            $dwnlCust = $this->repoDwnlCust->getById($custId);
            $mlmIdOwn = $dwnlCust->getHumanRef();
            $parentId = $dwnlCust->getParentId();
            $dwnlParen = $this->repoDwnlCust->getById($parentId);
            $mlmIdParent = $dwnlParen->getHumanRef();

            /* get nested composite parts */
            $balances = $this->getBalances($custId);
            $bonusStats = $this->getBonusStats($custId);
            $pension = $this->getPension($custId);

            /* compose API object */
            $data = new AData();
            $data->setMlmIdOwn($mlmIdOwn);
            $data->setMlmIdParent($mlmIdParent);
            $data->setBalances($balances);
            $data->setBonusStats($bonusStats);
            $data->setPension($pension);
            $result->setData($data);
            $respRes->setCode(AResponse::CODE_SUCCESS);
        } else {
            $respRes->setCode(AResponse::CODE_NO_DATA);
        }

        /** compose result */
        return $result;
    }

    private function getBalances($custId)
    {
        $result = [];
        $query = $this->qbGetBalances->build();
        $conn = $query->getConnection();
        $bind = [
            QBGetBalances::BND_CUST_ID => $custId
        ];
        $rs = $conn->fetchAll($query, $bind);
        if (is_array($rs)) {
            /** @var EAccount $one */
            foreach ($rs as $one) {
                $asset = $one[QBGetBalances::A_ASSET];
                $balance = $one[QBGetBalances::A_BALANCE];
                $item = new DBalanceItem();
                $item->setAsset($asset);
                $item->setValue($balance);
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @param int $custId
     * @return \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\BonusStats
     * @throws \Exception
     */
    private function getBonusStats($custId)
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\BonusStats();
        /* get the last complete forecast calculation ID */
        $query = $this->qbGetBonusStats->build();
        $conn = $query->getConnection();
        $rs = $conn->fetchRow($query);
        if ($rs) {
            $calcId = $rs[QBGetBonusStats::A_CALC_ID];
            $updated = $rs[QBGetBonusStats::A_DATE_UPDATED];
            $result->setDateUpdated($updated);
            /* get PV/TV/OV/Rank */
            $pv = $tv = $ov = 0;
            $rankCode = Cfg::RANK_DISTRIBUTOR;
            $byCalc = EBonDwnl::ATTR_CALC_REF . '=' . (int)$calcId;
            $byCust = EBonDwnl::ATTR_CUST_REF . '=' . (int)$custId;
            $rs = $this->repoBonDwnl->get("($byCalc) AND ($byCust)");
            if (count($rs)) {
                /** @var EBonDwnl $entry */
                $entry = reset($rs);
                $pv = $entry->getPv();
                $tv = $entry->getTv();
                $ov = $entry->getOv();
                $rankId = $entry->getRankRef();
                $rank = $this->repoRank->getById($rankId);
                $rankCode = $rank->getCode();
            }
            $result->setRank($rankCode);
            $result->setPv($pv);
            $result->setTv($tv);
            $result->setOv($ov);
        }
        return $result;
    }

    /**
     * @param int $custId
     * @return \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Pension
     * @throws \Exception
     */
    private function getPension($custId)
    {
        $result = new \Praxigento\Dcp\Api\Web\Report\Profile\Response\Data\Pension();
        $registry = $this->repoPension->getById($custId);
        if ($registry) {
            /* get data from repo */
            $left = $registry->getMonthsLeft();
            $since = $registry->getPeriodSince();
            $total = $registry->getMonthsTotal();
            $unq = $registry->getMonthsInact();

            /* format data for UI */
            $year = substr($since, 0, 4);
            $month = substr($since, 4, 2);
            $since = "$year/$month";

            /* compose API object */
            $result->setMonthLeft($left);
            $result->setMonthSince($since);
            $result->setMonthTotal($total);
            $result->setMonthUnq($unq);
        }
        return $result;
    }
}