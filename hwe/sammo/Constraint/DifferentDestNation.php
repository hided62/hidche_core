<?php

namespace sammo\Constraint;

class DifferentDestNation extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_NATION;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destNation['nation'] != $this->general['nation']){
            return true;
        }

        $this->reason = "이미 같은 국가입니다.";
        return false;
    }
}