<?php

namespace sammo\Constraint;

class NotNeutralDestCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_DEST_CITY;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->destCity)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in destCity");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destCity['nation'] != 0){
            return true;
        }

        $this->reason = "공백지입니다.";
        return false;
    }
}