<?php
namespace sammo\Command\UserAction;

use \sammo\Command;
use \sammo\Util;
use \sammo\JosaUtil;
use sammo\LastTurn;

class 휴식 extends Command\UserActionCommand{
    static protected $actionName = '휴식';

    protected function argTest():bool{
        return true;
    }

    protected function init(){
        //아무것도 하지 않음
        $this->fullConditionConstraints=[];

    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getCost():array{
        return [0, 0];
    }

    public function run(\Sammo\RandUtil $rng):bool{
        return true;
    }
}