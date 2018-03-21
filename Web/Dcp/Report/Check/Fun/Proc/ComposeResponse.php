<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report\Check\Fun\Proc;

use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Context as AContext;
use Praxigento\Dcp\Api\Web\Dcp\Report\Check\Response\Body as DBody;

/**
 * Process step to compose mined data into API response.
 */
class ComposeResponse
{

    public function exec(AContext $ctx): AContext
    {
        /* if current instance is active */
        if ($ctx->state == AContext::DEF_STATE_ACTIVE) {
            /* get working data from context */
            $period = $ctx->getPeriod();
            $customer = $ctx->respCustomer;
            $sections = $ctx->respSections;
            $resp = $ctx->getWebResponse();

            /* define local working data */
            $body = new DBody();
            $resp->setData($body);

            /* perform processing */
            $body->setPeriod($period);
            $body->setCustomer($customer);
            $body->setSections($sections);

            /* put result data into context */
        }
        return $ctx;
    }
}