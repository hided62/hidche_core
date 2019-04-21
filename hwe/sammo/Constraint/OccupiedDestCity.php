<?php

namespace sammo\Constraint;

class OccupiedDestCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_CITY;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in general");
        }

        if(!key_exists('nation', $this->destCity)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destCity['nation'] == $this->general['nation']){
            return true;
        }

        $this->reason = "아국이 아닙니다.";
        return false;
    }
}