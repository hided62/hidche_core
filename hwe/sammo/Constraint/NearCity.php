<?php

namespace sammo\Constraint;

class NearCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_CITY|Constraint::REQ_NUMERIC_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('city', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require city in general");
        }
        if($this->arg < 1 || !is_integer($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("arg should be >= 1 integer");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $dist = \sammo\searchDistance($this->general['city'], $this->arg, false);
        if(key_exists($this->destCity['city'], $dist)){
            return true;
        }

        if($this->arg == 1){
            $this->reason = "인접도시가 아닙니다.";
        }
        else{
            $this->reason = "거리가 너무 멉니다.";
        }
        
        return false;
    }
}