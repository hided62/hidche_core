<?php

namespace sammo\Constraint;
use \sammo\GameConst;

class NotOpeningPart extends Constraint{
    const REQ_VALUES = Constraint::REQ_INT_ARG;

    protected $relYear;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        $this->relYear = $this->arg;

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->relYear >= GameConst::$openingPartYear){
            return true;
        }

        $this->reason = "초반 제한 중에는 불가능합니다.";
        return false;
    }
}