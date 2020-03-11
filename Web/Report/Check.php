<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Report;

use Praxigento\Dcp\Api\Web\Report\Check\Context as AContext;
use Praxigento\Dcp\Api\Web\Report\Check\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Check\Response as AResponse;

class Check
    implements \Praxigento\Dcp\Api\Web\Report\CheckInterface
{

    /** @var \Praxigento\Core\Api\Helper\Customer\Currency */
    private $hlpCustCurrency;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\Authorize */
    private $procAuthorize;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\ComposeResponse */
    private $procComposeResp;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData */
    private $procMineData;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\ParseRequest */
    private $procParseRequest;

    public function __construct(
        \Praxigento\Core\Api\Helper\Customer\Currency $hlpCustCurrency,
        \Praxigento\Dcp\Web\Report\Check\A\Authorize $procAuthorize,
        \Praxigento\Dcp\Web\Report\Check\A\ComposeResponse $procComposeResp,
        \Praxigento\Dcp\Web\Report\Check\A\MineData $procMineData,
        \Praxigento\Dcp\Web\Report\Check\A\ParseRequest $procParseRequest
    ) {
        $this->hlpCustCurrency = $hlpCustCurrency;
        $this->procAuthorize = $procAuthorize;
        $this->procComposeResp = $procComposeResp;
        $this->procMineData = $procMineData;
        $this->procParseRequest = $procParseRequest;
    }

    public function exec($request)
    {
        /* DON"T USE THIS CODE AS TEMPLATE FOR OTHER SERVICES (old stylish) */
        assert($request instanceof ARequest);
        /* prepare processing context */
        $ctx = new AContext();
        $ctx->setWebRequest($request);
        $ctx->setWebResponse(new AResponse());
        $ctx->state = AContext::DEF_STATE_ACTIVE;

        /* perform processing: step by step */
        $this->procParseRequest->exec($ctx);
        $this->procAuthorize->exec($ctx);
        $this->procMineData->exec($ctx);
        $this->procComposeResp->exec($ctx);
        $custId = $ctx->getCustomerId();
        $currency = $this->hlpCustCurrency->getCurrency($custId);
        $currencyBase = $this->hlpCustCurrency->getCurrencyBase();

        /* get result from context */
        $result = $ctx->getWebResponse();
        $respRes = $result->getResult();
        $data = $result->getData();
        $cust = $data->getCustomer();
        $data->setCurrency($currency);
        $data->setCurrencyBase($currencyBase);

        if (is_null($cust)) {
            $respRes->setCode(AResponse::CODE_NO_DATA);
            $result->setData(null);
        } else {
            $respRes->setCode(AResponse::CODE_SUCCESS);
        }
        return $result;
    }

}
