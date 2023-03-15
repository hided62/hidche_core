<?php

namespace sammo\Constraint;

class NeutralCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
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

        if($this->city['nation'] == 0){
            return true;
        }

        $this->reason = "공백지가 아닙니다.";
        return false;
    }
}