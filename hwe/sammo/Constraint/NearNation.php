<?php

namespace sammo\Constraint;

class NearNation extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_DEST_NATION;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('capital', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require capital in nation");
        }

        if(!key_exists('capital', $this->destNation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require capital in destNation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $srcCityID = $this->nation['capital'];
        $srcNationID = $this->nation['nation'];

        $destCityID = $this->destNation['capital'];
        $destNationID = $this->destNation['nation'];
        
        $dist = \sammo\searchDistanceListToDest($srcCityID, $destCityID, [$srcNationID, $destNationID]);

        if(!$dist){
            $this->reason = "인접 국가가 아닙니다.";
            return false;
        }
        
        return false;
    }
}