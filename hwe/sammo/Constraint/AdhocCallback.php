<?php

namespace sammo\Constraint;

//일일히 클래스를 만들기 싫을 때 간단히 끝내는 Constraint

class AdhocCallback extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!is_callable($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require callback");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $reason = ($this->arg)();
        if($reason === null){
            return true;
        }

        $this->reason = $reason;
        return false;
    }
}