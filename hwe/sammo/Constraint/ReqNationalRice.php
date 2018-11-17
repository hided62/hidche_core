<?php

namespace sammo\Constraint;

class ReqNationRice extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_NUMERIC_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('rice', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require rice in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->nation['rice'] < $this->arg){
            return true;
        }

        $this->reason = "병량이 부족합니다.";
        return false;
    }
}