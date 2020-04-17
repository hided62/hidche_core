<?php

namespace sammo\Constraint;

class NotLord extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('officer_level', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require officer_level in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['officer_level'] != 12){
            return true;
        }

        $this->reason = "군주입니다.";
        return false;
    }
}