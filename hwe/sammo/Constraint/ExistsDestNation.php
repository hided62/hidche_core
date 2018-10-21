<?php

namespace sammo\Constraint;

class ExistsDestGeneral extends Constraint{
    const REQ_VALUES = Constraint::REQ_DEST_NATION;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destNation['nation']){
            return true;
        }

        $this->reason = "없는 국가입니다.";
        return false;
    }
}