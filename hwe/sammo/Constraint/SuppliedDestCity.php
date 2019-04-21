<?php

namespace sammo\Constraint;

class SuppliedDestCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_DEST_CITY;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('supply', $this->destCity)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require supply in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destCity['supply']){
            return true;
        }

        $this->reason = "고립된 도시입니다.";
        return false;
    }
}