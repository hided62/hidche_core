<?php

namespace sammo\Constraint;
use \sammo\DB;

class BattleGroundCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_CITY|Contraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('nation', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in general");
        }

        if(!key_exists('nation', $this->destCity)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in dest city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $nationID = $this->general['nation'];
        $destNationID = $this->city['nation'];
        if($destNationID == 0){
            return true;
        }

        if($this->arg && $destNationID == $nationID){
            return true;
        }

        $db = DB::db();
        $diplomacy = $db->queryFirstField('SELECT state FROM diplomacy WHERE me = %i AND you = %i', $nationID, $destNationID);
        if($diplomacy === 0){
            return true;
        }

        $this->reason = "교전중인 국가의 도시가 아닙니다.";
        return false;
    }
}