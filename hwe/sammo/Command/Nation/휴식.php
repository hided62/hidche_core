<?php
namespace sammo\Command\Nation;

use \sammo\Command;
use \sammo\Util;
use \sammo\JosaUtil;

class 휴식 extends NationCommand{
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
        return true;
    }
}