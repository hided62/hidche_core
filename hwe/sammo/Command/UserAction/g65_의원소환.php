<?php
namespace sammo\Command\UserAction;

use sammo\ActionBuff\g65_내정성공;
use \sammo\Command;
use sammo\DB;

class g65_의원소환 extends Command\UserActionCommand{
    static protected $actionName = '의원 소환';

    protected function argTest():bool{
        return true;
    }

    public function getBrief(): string
    {
        return '의원 소환';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "부상 시 치료(재사용 대기 {$postReqTurn})";
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
        if($general->getVar('injury') == 0){
            return false;
        }
        $general->setVar('injury', 0);
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $this->generalObj->getLogger();
        $logger->pushGeneralActionLog("의원을 불러 부상을 치료합니다. <1>$date</>");
        $general->applyDB(DB::db());
        return true;
    }
}