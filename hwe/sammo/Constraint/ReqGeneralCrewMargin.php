<?php

namespace sammo\Constraint;
use sammo\GameConst;
use sammo\GameUnitConst;
use sammo\General;

class ReqGeneralCrewMargin extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        foreach(['leader','power','intel','crew','crewtype','nation','level'] as $key){
            if(!key_exists($key, $this->general)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require {$key} in general");
            }
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($crewType != $this->general['crewtype']){
            return true;
        }

        //XXX: 왜 General -> obj -> General 변환을 하고 있나?
        $generalObj = new General($this->general, null, null, null, false);
        $leadership = $generalObj->getLeadership();
        $crew = $this->general['crew'];

        if($leadership * 100 > $crew){
            return true;
        }

        $this->reason = "이미 많은 병력을 보유하고 있습니다.";
        return false;
    }
}