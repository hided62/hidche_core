<?php

namespace sammo\Constraint;

class BeOpeningPart extends Constraint{
    const REQ_VALUES = Constraint::REQ_INT_ARG;

    protected $relYear;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        $this->relYear = $this->arg;

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->relYear < GameConst::$openingPartYear){
            return true;
        }

        $this->reason = "초반이 지났습니다.";
        return false;
    }
}