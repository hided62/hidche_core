<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\DB;
class DisallowDiplomacyBetweenStatus extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARRAY_ARG|Constraint::REQ_NATION|Constraint::REQ_DEST_NATION;

    protected $nationID;
    protected $destNationID;
    protected $disallowStatus = [];

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        $this->nationID = $this->nation['nation'];
        $this->destNationID = $this->destNation['nation'];
        $this->disallowStatus = $this->arg;

        foreach($this->disallowStatus as $dipCode => $errMsg){
            if(!is_int($dipCode)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("dipCode $dipCode must be int");
            }
            if(!is_string($errMsg)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("dipCode $errMsg must be string");
            }
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();

        $state = $db->queryFirstField(
            'SELECT state FROM diplomacy WHERE me = %i AND you = %i AND `state` IN %li LIMIT 1', 
            $this->nationID, 
            $this->destNationID, 
            array_keys($this->disallowStatus)
        );
        if($state === null){
            return true;
        }
        $this->msg = $this->disallowStatus[$state];
        return false;
    }
}