<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Test\Praxigento\Dcp\Web\Report;

use Praxigento\Core\Api\App\Web\Request\Dev as AReqDev;
use Praxigento\Dcp\Api\Web\Report\Accounting\Request as ARequest;
use Praxigento\Dcp\Api\Web\Report\Accounting\Request\Data as AReqData;
use Praxigento\Dcp\Api\Web\Report\Accounting\Response as AResponse;
use Praxigento\Dcp\Web\Report\Accounting as AService;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class AccountingTest
    extends \Praxigento\Core\Test\BaseCase\Manual
{

    public function test_execute()
    {
        $this->setAreaCode();
        $req = new ARequest();
        $reqData = new AReqData();
        $reqDev = new AReqDev();
        $req->setData($reqData);
        $req->setDev($reqDev);
        $reqData->setPeriod('201809');
        $reqDev->setCustId(82);
        /** @var AService $serv */
        $serv = $this->manObj->get(AService::class);
        $def = $this->manTrans->begin();
        $resp = $serv->exec($req);
        $this->manTrans->rollback($def);
        $this->assertInstanceOf(AResponse::class, $resp);
    }
}