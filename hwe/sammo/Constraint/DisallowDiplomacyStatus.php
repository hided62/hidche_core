<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\DB;
class NotDiplomacyStatus extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARG|Constraint::REQ_NATION;

    protected $disallowStatus;
    protected $msg;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(count($this->arg) != 2){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require disallowDipStatusArray, message in args");
        }

        if(!is_array($this->arg[0])){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("disallowDipStatusArray must be array");
        }

        if(!is_string($this->arg[1])){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("message must be string");
        }

        $this->disallowStatus = [];
        foreach($this->arg[0] as $status){
            if(!is_int($status)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("{$status} must be int");
            }
            $this->disallowStatus[] = $status;
        }
        $this->msg = $this->arg[1];

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();

        $disallowCnt = $db->queryFirstField(
            'SELECT count(*) FROM diplomacy WHERE me = %i AND `state` IN %li', 
            $this->nation['nation'], 
            $this->disallowStatus
        );
        if($disallowCnt == 0){
            return true;
        }
        $this->msg = "민심이 낮아 주민들이 도망갑니다.";
        return false;
    }
}