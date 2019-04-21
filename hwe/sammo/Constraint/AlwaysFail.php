<?php

namespace sammo\Constraint;

//일일히 클래스를 만들기 싫을 때 간단히 끝내는 Constraint

class AlwaysFail extends Constraint{
    const REQ_VALUES = Constraint::REQ_STRING_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $this->reason = $this->arg;
        return false;
    }
}