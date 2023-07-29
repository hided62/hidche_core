<?php

namespace sammo\Constraint;

use \sammo\GameUnitConst;
use \sammo\DB;
use \sammo\KVStorage;
use \sammo\CityConst;

class AvailableRecruitCrewType extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in nation");
        }

        if(!key_exists('tech', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require tecg in nation");
        }

        if(GameUnitConst::byID($this->arg) === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("invalid crewtype");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        [$startyear, $year] = $gameStor->getValuesAsArray(['startyear', 'year']);

        $nationID = $this->nation['nation'];
        $tech = $this->nation['tech'];

        $ownCities = [];
        $ownRegions = [];
        foreach($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i', $nationID) as $ownCity){
            $ownCities[$ownCity] = 1;
            $ownRegions[CityConst::byId($ownCity)->region] = 1;
        }

        $crewType = GameUnitConst::byID($this->arg);
        if($crewType->isValid($ownCities, $ownRegions, $year - $startyear, $tech)){
            return true;
        }

        $this->reason = "현재 선택할 수 없는 병종입니다.";
        return false;
    }
}
