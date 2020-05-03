<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\DB;
class AllowDiplomacyWithTerm extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARRAY_ARG|Constraint::REQ_NATION|Constraint::REQ_DEST_NATION;

    protected $nationID;
    protected $destNationID;
    protected $allowDipCode;
    protected $allowMinTerm;
    protected $errMsg = '';

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(count($this->arg) != 3){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require allowDipCode, term, errMsg tuple");
        }

        $this->nationID = $this->nation['nation'];
        $this->destNationID = $this->destNation['nation'];
        [$this->allowDipCode, $this->allowMinTerm, $this->errMsg] = $this->arg;

        
        if(!is_int($this->allowDipCode)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("dipCode {$this->allowDipCode} must be int");
        }
        if(!is_int($this->allowMinTerm)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("term {$this->allowMinTerm} must be int");
        }
        if($this->allowMinTerm < 0){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("term {$this->allowMinTerm} must be not negative");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();


        $valid = false;
        $state = $db->queryFirstField(
            'SELECT state FROM diplomacy WHERE me = %i AND you = %i AND `state` = %i AND `term` >= %i', 
            $this->nationID, 
            $this->destNationID, 
            $this->allowDipCode,
            $this->allowMinTerm
        );
        if($state !== null){
            return true;
        }
        $this->reason = $this->errMsg;
        return false;
    }
}