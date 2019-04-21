<?php

namespace sammo\Constraint;

class ReqNationGold extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_NUMERIC_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('gold', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require gold in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->nation['gold'] < $this->arg){
            return true;
        }

        $this->reason = "국고가 부족합니다.";
        return false;
    }
}