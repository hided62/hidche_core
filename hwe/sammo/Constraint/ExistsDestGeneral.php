<?php

namespace sammo\Constraint;

class ExistsDestGeneral extends Constraint{
    const REQ_VALUES = Constraint::REQ_DEST_GENERAL;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destGeneral['no']){
            return true;
        }

        $this->reason = "없는 장수입니다.";
        return false;
    }
}