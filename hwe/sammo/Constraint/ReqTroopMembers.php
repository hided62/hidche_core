<?php

namespace sammo\Constraint;
use sammo\DB;

class ReqTroopMembers extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('troop', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require troop in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();
        //NOTE:이 경우에 DB를 사용하지 않을 수 있는가?
        $troopMember = $db->queryFirstField('SELECT no FROM troop WHERE troop = %i AND no != %i LIMIT 1', $this->general['troop'], $this->general['no']);

        if($troopMember !== null){
            return true;
        }

        $this->reason = "집합 가능한 부대원이 없습니다.";
        return false;
    }
}