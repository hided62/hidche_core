<?php

namespace sammo\Constraint;

class NearNation extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_DEST_NATION;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $srcNationID = $this->nation['nation'];
        $destNationID = $this->destNation['nation'];

        if(!\sammo\isNeighbor($srcNationID, $destNationID, false)){
            $this->reason = "인접 국가가 아닙니다.";
            return false;
        }
        
        return true;
    }
}