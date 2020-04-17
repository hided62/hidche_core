<?php

namespace sammo\Constraint;

class NotCapital extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_NATION|Constraint::REQ_BOOLEAN_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('city', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require city in general");
        }

        if(!key_exists('officer_level', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require officer_level in general");
        }

        if(!key_exists('capital', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require capital in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->nation['capital'] != $this->general['city']){
            return true;
        }

        if($this->arg && 2 <= $this->general['officer_level'] && $this->general['officer_level'] <= 4){
            return true;
        }

        $this->reason = "이미 수도입니다.";
        return false;
    }
}