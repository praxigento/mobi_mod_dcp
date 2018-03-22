<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Web\Dcp\Report;

use Praxigento\Dcp\Api\Web\Dcp\Report\Pension\Request as ARequest;
use Praxigento\Dcp\Api\Web\Dcp\Report\Pension\Response as AResponse;

class Pension
    implements \Praxigento\Dcp\Api\Web\Dcp\Report\PensionInterface
{
    public function __construct()
    {
    }

    public function exec($request)
    {
        assert($request instanceof ARequest);

        $result = new AResponse();
        return $result;
    }

}