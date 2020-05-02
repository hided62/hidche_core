<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\DB;
class AllowDiplomacyBetweenStatus extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARRAY_ARG|Constraint::REQ_NATION|Constraint::REQ_DEST_NATION;

    protected $nationID;
    protected $destNationID;
    protected $allowDipCodeList = [];
    protected $errMsg = '';

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(count($this->arg) != 2){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require allowDipCodeList, errMsg pair");
        }

        $this->nationID = $this->nation['nation'];
        $this->destNationID = $this->destNation['nation'];
        [$this->allowDipCodeList, $this->errMsg] = $this->arg;

        foreach($this->allowDipCodeList as $dipCode){
            if(!is_int($dipCode)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("dipCode $dipCode must be int");
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
            $this->allowDipCodeList
        );
        if($state !== null){
            return true;
        }
        $this->reason = $this->errMsg;
        return false;
    }
}