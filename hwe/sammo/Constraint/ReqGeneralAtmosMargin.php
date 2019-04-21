<?php

namespace sammo\Constraint;
use sammo\GameConst;

class ReqGeneralAtmosMargin extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('atmos', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require atmos in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['atmos'] < $this->arg){
            return true;
        }

        $this->reason = "이미 사기는 하늘을 찌를듯 합니다.";
        return false;
    }
}