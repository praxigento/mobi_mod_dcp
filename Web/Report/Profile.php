<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\Dcp\Api\Web\Report\Profile\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Profile\Response as AResponse;
use Praxigento\Dcp\Api\Web\Report\Profile\Response\Data as AData;

class Profile
    implements \Praxigento\Dcp\Api\Web\Report\ProfileInterface
{
    /** @var \Praxigento\Core\Api\App\Web\Authenticator\Front */
    private $authenticator;
    /** @var \Praxigento\PensionFund\Repo\Entity\Registry */
    private $repoPension;

    public function __construct(
        \Praxigento\Core\Api\App\Web\Authenticator\Front $authenticator,
        \Praxigento\PensionFund\Repo\Entity\Registry $repoPension
    )
    {
        $this->authenticator = $authenticator;
        $this->repoPension = $repoPension;
    }

    public function exec($request)
    {
        assert($request instanceof ARequest);
        $result = new AResponse();
        $respRes = $result->getResult();

        /** perform processing */
        $custId = $this->authenticator->getCurrentUserId($request);
        $registry = $this->repoPension->getById($custId);
        if ($registry) {
            /* get data from repo */
            $amountIn = $registry->getAmountIn();
            $amountPercent = $registry->getAmountPercent();
            $amountReturned = $registry->getAmountReturned();
            $balanceClose = $registry->getBalanceClose();
            $balanceOpen = $registry->getBalanceOpen();
            $left = $registry->getMonthsLeft();
            $since = $registry->getPeriodSince();
            $total = $registry->getMonthsTotal();
            $unq = $registry->getMonthsInact();

            /* format data for UI */
            $year = substr($since, 0, 4);
            $month = substr($since, 4, 2);
            $since = "$year/$month";

            /* compose API object */
            $data = new AData();
            $data->setAmountIn($amountIn);
            $data->setAmountPercent($amountPercent);
            $data->setAmountReturned($amountReturned);
            $data->setBalanceClose($balanceClose);
            $data->setBalanceOpen($balanceOpen);
            $data->setMonthLeft($left);
            $data->setMonthSince($since);
            $data->setMonthTotal($total);
            $data->setMonthUnq($unq);

            $result->setData($data);
            $respRes->setCode(AResponse::CODE_SUCCESS);
        } else {
            $respRes->setCode(AResponse::CODE_NO_DATA);
        }

        /** compose result */
        return $result;
    }

}