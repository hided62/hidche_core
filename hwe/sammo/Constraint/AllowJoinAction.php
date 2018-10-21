<?php

namespace sammo\Constraint;
use \sammo\GameConst;

class AllowJoinAction extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('makelimit', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require makelimit in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['makelimit'] == 0){
            return true;
        }

        $joinActionLimit = GameConst::$joinActionLimit;

        $this->reason = "재야가 된지 {$joinActionLimit}턴이 지나야 합니다.";
        return false;
    }
}