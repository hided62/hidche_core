<?php

namespace sammo\Constraint;

use \sammo\DB;

class HasRouteWithEnemy extends Constraint{
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

        $destCityNation = $db->queryFirstField('SELECT nation FROM city WHERE city = %i', $this->destCity['city']);
        if($destCityNation !== 0 && $destCityNation !== $this->general['nation'] && !in_array($destCityNation, $allowedNationList)){
            $this->reason = "교전중인 국가가 아닙니다.";
            return false;
        }

        $distanceList = \sammo\searchDistanceListToDest($this->general['city'], $this->destCity['city'], $allowedNationList);
        if(!$distanceList){
            $this->reason = "경로에 도달할 방법이 없습니다.";
            return false;
        }

        return true;
    }
}