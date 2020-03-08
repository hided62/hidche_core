<?php

namespace sammo\Constraint;

use \sammo\DB;

class HasRoute extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_CITY;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('city', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require city in general");
        }

        if(!key_exists('nation', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();

        $allowedNationList = $db->queryFirstColumn('SELECT you FROM diplomacy WHERE state = 0 AND me = %i', $this->general['nation']);
        $allowedNationList[] = $this->general['nation'];
        $allowedNationList[] = 0;

        $distanceList = \sammo\searchDistanceListToDest($this->general['city'], $this->destCity['city'], $allowedNationList);
        if(!$distanceList){
            $this->reason = "경로에 도달할 방법이 없습니다.";
            return false;
        }

        return true;
    }
}