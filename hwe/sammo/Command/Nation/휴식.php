<?php
namespace sammo\Command\Nation;

use \sammo\Command;
use \sammo\Util;
use \sammo\JosaUtil;
use sammo\LastTurn;

class 휴식 extends Command\NationCommand{
    static protected $actionName = '휴식';

    protected function argTest():bool{
        return true;
    }

    protected function init(){
        //아무것도 하지 않음
        $this->runnableConstraints=[];
        
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

    public function run():bool{
        return true;
    }
}