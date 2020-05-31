<?php
namespace sammo\Command\General;

use \sammo\Command;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\LastTurn;
use \sammo\DB;

class 휴식 extends Command\GeneralCommand{
    static protected $actionName = '휴식';
    
    protected function argTest():bool{
        return true;
    }

    protected function init()
    {
        $this->minConditionConstraints=[];
        $this->fullConditionConstraints=[];
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run():bool{
        $general = $this->generalObj;
        $logger = $general->getLogger();
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $logger->pushGeneralActionLog("아무것도 실행하지 않았습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn());

        $general->applyDB(DB::db());
        return true;
    }
}