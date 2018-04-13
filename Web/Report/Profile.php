<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\Accounting\Repo\Data\Account as EAccount;
use Praxigento\BonusHybrid\Repo\Data\Downline as EBonDwnl;
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
    /** @var \Praxigento\BonusHybrid\Repo\Dao\Downline */
    private $daoBonDwnl;
    /** @var \Praxigento\Downline\Repo\Dao\Customer */
    private $daoDwnlCust;
    /** @var \Praxigento\PensionFund\Repo\Dao\Registry */
    private $daoPension;
    /** @var \Praxigento\BonusBase\Repo\Dao\Rank */
    private $daoRank;
    /** @var \Praxigento\Core\Api\Helper\Customer\Currency */
    private $hlpCustCurrency;
    /** @var \Praxigento\Dcp\Api\Helper\Map */
    private $hlpDcpMap;
    /** @var \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBalances */
    private $qbGetBalances;
    /** @var \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBonusStats */
    private $qbGetBonusStats;

    public function __construct(
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\BonusBase\Repo\Dao\Rank $daoRank,
        \Praxigento\BonusHybrid\Repo\Dao\Downline $daoBonDwnl,
        \Praxigento\Downline\Repo\Dao\Customer $daoDwnlCust,
        \Praxigento\PensionFund\Repo\Dao\Registry $daoPension,
        \Praxigento\Core\Api\Helper\Customer\Currency $hlpCustCurrency,
        \Praxigento\Dcp\Api\Helper\Map $hlpDcpMap,
        \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBalances $qbGetBalances,
        \Praxigento\Dcp\Web\Report\Profile\A\Query\GetBonusStats $qbGetBonusStats
    ) {
        $this->authenticator = $authenticator;
        $this->daoRank = $daoRank;
        $this->daoBonDwnl = $daoBonDwnl;
        $this->daoDwnlCust = $daoDwnlCust;
        $this->daoPension = $daoPension;
        $this->hlpCustCurrency = $hlpCustCurrency;
        $this->hlpDcpMap = $hlpDcpMap;
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
            $dwnlCust = $this->daoDwnlCust->getById($custId);
            $mlmIdOwn = $dwnlCust->getMlmId();
            $parentId = $dwnlCust->getParentId();
            $dwnlParen = $this->daoDwnlCust->getById($parentId);
            $mlmIdParent = $dwnlParen->getMlmId();

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
                /* parse DB data */
                $asset = $one[QBGetBalances::A_ASSET];
                $currency = $one[QBGetBalances::A_ASSET_CURR];
                $value = $one[QBGetBalances::A_BALANCE];
                /* trash code :( */
                if ($currency) {
                    /**
                     * Currency is null for not-money-assets (PV),
                     * convert asset value from asset currency to customer currency
                     */
                    $value = $this->hlpCustCurrency->convertFromBase($value, $custId);
                    $currency = $this->hlpCustCurrency->getCurrency($custId);
                }

                /* compose API data */
                $item = new DBalanceItem();
                $item->setAsset($asset);
                $item->setCurrency($currency);
                $item->setValue($value);
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
            $updated .= ' UTC'; // see \Praxigento\BonusBase\Repo\Dao\Calculation::markComplete
            $result->setDateUpdated($updated);
            /* get PV/TV/OV/Rank */
            $pv = $tv = $ov = 0;
            $rankCode = Cfg::RANK_DISTRIBUTOR;
            $byCalc = EBonDwnl::A_CALC_REF . '=' . (int)$calcId;
            $byCust = EBonDwnl::A_CUST_REF . '=' . (int)$custId;
            $rs = $this->daoBonDwnl->get("($byCalc) AND ($byCust)");
            if (count($rs)) {
                /** @var EBonDwnl $entry */
                $entry = reset($rs);
                $pv = $entry->getPv();
                $tv = $entry->getTv();
                $ov = $entry->getOv();
                $rankId = $entry->getRankRef();
                $rank = $this->daoRank->getById($rankId);
                $rankCode = $rank->getCode();

            }
            $rankUiCode = $this->hlpDcpMap->rankCodeToUi($rankCode);
            $result->setRank($rankUiCode);
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
        $registry = $this->daoPension->getById($custId);
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