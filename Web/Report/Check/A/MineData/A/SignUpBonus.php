<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2020
 */

namespace Praxigento\Dcp\Web\Report\Check\A\MineData\A;

use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignupBonus as DSignUpBonus;
use Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignUpBonus\Item as DItem;
use Praxigento\Dcp\Config as Cfg;
use Praxigento\Dcp\Web\Report\Check\A\MineData\A\SignupBonus\A\Query as AQuery;

/**
 * Action to build "SignUp Bonus" section of the DCP's "Check" report.
 */
class SignUpBonus {
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs */
    private $aHlpGetCalcs;
    /** @var \Praxigento\Dcp\Web\Report\Check\A\MineData\A\SignupBonus\A\Query */
    private $aQuery;
    /** @var \Praxigento\Core\Api\Helper\Customer\Currency */
    private $hlpCustCurrency;
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;

    public function __construct(
        \Praxigento\Core\Api\Helper\Customer\Currency $hlpCustCurrency,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\Z\Helper\GetCalcs $aHlpGetCalcs,
        \Praxigento\Dcp\Web\Report\Check\A\MineData\A\SignupBonus\A\Query $aQuery
    ) {
        $this->hlpCustCurrency = $hlpCustCurrency;
        $this->hlpPeriod = $hlpPeriod;
        $this->aHlpGetCalcs = $aHlpGetCalcs;
        $this->aQuery = $aQuery;
    }

    /**
     * @param int $custId
     * @param string $period YYYYMM
     * @return \Praxigento\Dcp\Api\Web\Report\Check\Response\Body\Sections\SignupBonus
     * @throws \Exception
     */
    public function exec($custId, $period) {
        /* get input and prepare working data */
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);

        /* default values for result attributes */
        $totalBase = $total = 0;
        $items = [];

        /* perform processing */
        $calcs = $this->aHlpGetCalcs->exec($dsBegin, $dsEnd);
        if (isset($calcs[Cfg::CODE_TYPE_CALC_BONUS_SIGN_UP_CREDIT])) {
            $calcCredit = $calcs[Cfg::CODE_TYPE_CALC_BONUS_SIGN_UP_CREDIT];
            [$totalBase, $total, $items] = $this->getItems($calcCredit, $custId, $dsEnd);
        }

        /* compose result */
        $result = new DSignUpBonus();
        $result->setItems($items);
        $result->setTotalAmount($total);
        $result->setTotalAmountBase($totalBase);

        return $result;
    }

    private function getItems($calcId, $custId, $dsEnd) {
        $totalBase = $total = 0;
        $items = [];

        $query = $this->aQuery->build();
        $conn = $query->getConnection();
        $bind = [
            AQuery::BND_CALC_ID => $calcId,
            AQuery::BND_CUST_ID => $custId
        ];
        $rs = $conn->fetchAll($query, $bind);

        foreach ($rs as $one) {
            /* get DB data */
            $amountBase = $one[AQuery::A_AMOUNT];
            $note = $one[AQuery::A_NOTE];

            /* calculated values */
            $ds = $dsEnd;
            $yyyy = substr($ds, 0, 4); // YYYY
            $mm = substr($ds, 4, 2); // MM
            $dd = substr($ds, 6, 2); // DD
            $date = "$yyyy-$mm-$dd";
            $amount = $this->hlpCustCurrency->convertFromBase($amountBase, $custId, true, $date);
            $totalBase += $amountBase;
            $total += $amount;

            /* compose API data */
            $item = new DItem();
            $item->setAmount($amount);
            $item->setAmountBase($amountBase);
            $item->setNote($note);
            $items[] = $item;
        }
        return [$totalBase, $total, $items];
    }
}
