<?php

namespace sammo\Constraint;
use sammo\GameConst;
use sammo\GameUnitConst;
use sammo\General;

class ReqGeneralCrewMargin extends Constraint{

    protected $crewType;
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(GameUnitConst::byID($this->arg) === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("{$this->arg} is invalid crewtype");
        }

        foreach(['leadership','strength','intel','crew','crewtype','nation','officer_level'] as $key){
            if(!key_exists($key, $this->general)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require {$key} in general");
            }
        }

        $this->crewType = $this->arg;

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;
        $reqCrewType = GameUnitConst::byID($this->arg);

        //XXX: 왜 General -> obj -> General 변환을 하고 있나?
        $generalObj = new General($this->general, null, null, null, null, null, false);

        if($reqCrewType->id != $generalObj->getCrewTypeObj()->id){
            return true;
        }

        $leadership = $generalObj->getLeadership();
        $crew = $this->general['crew'];

        if($leadership * 100 > $crew){
            return true;
        }

        $this->reason = "이미 많은 병력을 보유하고 있습니다.";
        return false;
    }
}