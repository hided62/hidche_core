<?php
namespace sammo\Command\General;

use \sammo\Command;
use \sammo\Util;
use \sammo\JosaUtil;

class 휴식 extends GeneralCommand{
    protected function init(){
        //아무것도 하지 않음
    }

    protected function argTest():bool{
        return true;
    }

    public function getCost():array{
        return [0, 0];
    }

    public function run():bool{
        $general = $this->generalObj;
        $logger = $general->getLogger();
        $date = substr($general->getVar('turntime'),11,5);
        $logger->pushGeneralActionLog("아무것도 실행하지 않았습니다. <1>$date</>");

        $general->increaseVar('killturn', -1);
        $general->applyDB(DB::db());
        return true;
    }
}