<?php

namespace sammo\Constraint;
use sammo\GameConst;

class ReqGeneralTrainMargin extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('train', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require train in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['train'] < $this->arg){
            return true;
        }

        $this->reason = "병사들은 이미 정예병사들입니다.";
        return false;
    }
}