<?php

namespace sammo\Constraint;

class NotOccupiedCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_CITY;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in general");
        }

        if(!key_exists('nation', $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->city['nation'] != $this->general['nation']){
            return true;
        }

        $this->reason = "아국입니다.";
        return false;
    }
}