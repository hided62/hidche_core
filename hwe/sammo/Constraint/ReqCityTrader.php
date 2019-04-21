<?php

namespace sammo\Constraint;

class ReqCityTrader extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('trade', $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require trade in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->city['trade'] !== null || $this->arg >= 2){
            return true;
        }

        $this->reason = "도시에 상인이 없습니다.";
        return false;
    }
}