<?php
namespace sammo\Command\UserAction;

use \sammo\Command;
use sammo\DB;
use sammo\Util;

class g65_군량급매 extends Command\UserActionCommand{
    static protected $actionName = '군량급매';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '군량 급매';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "금/쌀 동등화(재사용 대기 {$postReqTurn})";
    }

    protected function init(){
        //아무것도 하지 않음
        $this->fullConditionConstraints=[];

    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 60;
    }

    public function getCost():array{
        return [0, 0];
    }

    public function run(\Sammo\RandUtil $rng):bool{
        $general = $this->generalObj;

        $date = $general->getTurnTime($general::TURNTIME_HM);

        $gold = $general->getVar('gold');
        $rice = $general->getVar('rice');

        $avg = Util::toInt(($gold + $rice + 1) / 2);
        $general->setVar('gold', $avg);
        $general->setVar('rice', $avg);

        $logger = $this->generalObj->getLogger();
        $logger->pushGeneralActionLog("지나가는 상인과 금과 쌀을 거래합니다. <1>$date</>");
        $general->applyDB(DB::db());
        return true;
    }
}