<?php

namespace sammo\Constraint;

class ReqGeneralCrew extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('crew', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require crew in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['crew'] > 0){
            return true;
        }

        $this->reason = "병사가 모자랍니다.";
        return false;
    }
}