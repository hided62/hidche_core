<?php

namespace sammo\Constraint;

class OccupiedCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_CITY;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
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

        //특수 예외로 ARG가 지정된 경우에는 재야여도 'OccupiedCity'로 허용함
        if($this->arg && $this->general['nation'] == 0){
            return true;
        }

        if($this->city['nation'] == $this->general['nation']){
            return true;
        }

        $this->reason = "아국이 아닙니다.";
        return false;
    }
}