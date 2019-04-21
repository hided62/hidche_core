<?php

namespace sammo\Constraint;
use \sammo\DB;

class CheckNationNameDuplicate extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_STRING_ARG;

    protected $relYear;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();

        $exists = $db->queryFirstField('SELECT count(*) FROM nation WHERE name = %s AND nation != %i', $this->arg, $this->nation['nation']);

        if($exists == 0){
            return true;
        }

        $this->reason = '존재하는 국가명입니다.';
        return false;
    }
}