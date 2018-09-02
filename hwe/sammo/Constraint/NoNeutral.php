<?php

namespace sammo\Constraint;

class NoOpeningPart extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('level', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require level in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['level'] != 0){
            return true;
        }

        $this->reason = "재야입니다.";
        return false;
    }
}