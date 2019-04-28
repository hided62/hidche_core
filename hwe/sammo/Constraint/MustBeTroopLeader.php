<?php

namespace sammo\Constraint;
use sammo\DB;
//TODO: DB 사용하지 않도록 변경

class MustBeTroopLeader extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('troop', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require troop in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['no'] == $this->general['troop']){
            return true;
        }

        $this->reason = "부대장이 아닙니다.";
        return false;
    }
}