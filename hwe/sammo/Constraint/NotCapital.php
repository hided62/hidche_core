<?php

namespace sammo\Constraint;

class NotCapital extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_NATION|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('city', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require city in general");
        }

        if(!key_exists('level', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require level in general");
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

        if($this->arg && 2 <= $this->general['level'] && $this->general['level'] <= 4){
            return true;
        }

        $this->reason = "이미 수도입니다.";
        return false;
    }
}