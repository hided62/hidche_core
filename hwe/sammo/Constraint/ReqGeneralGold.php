<?php

namespace sammo\Constraint;

class ReqGeneralGold extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_NUMERIC_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('gold', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require gold in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['gold'] < $this->arg){
            return true;
        }

        $this->reason = "자금이 모자랍니다.";
        return false;
    }
}