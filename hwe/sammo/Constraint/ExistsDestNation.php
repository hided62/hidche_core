<?php

namespace sammo\Constraint;

class ExistsDestNation extends Constraint{
    const REQ_VALUES = 0;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destNation['nation']??0){
            return true;
        }

        $this->reason = "멸망한 국가입니다.";
        return false;
    }
}