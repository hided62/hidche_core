<?php

namespace sammo\Constraint;

class BeLord extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
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

        if($this->general['level'] != 12){
            return true;
        }

        $this->reason = "군주가 아닙니다.";
        return false;
    }
}