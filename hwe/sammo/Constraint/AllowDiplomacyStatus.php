<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\DB;
class AllowDiplomacyStatus extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARRAY_ARG;

    protected $nationID;
    protected $allowStatus = [];

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(count($this->arg) != 3){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nationID, allowStatus, errMsg tuple");
        }

        [$this->nationID, $this->allowStatus, $this->errMsg] = $this->arg;
        if(!is_int($this->nationID)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("nationID {$this->nationID} must be int");
        }

        if(!is_array($this->allowStatus)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("allowStatus {$this->allowStatus} must be array");
        }

        if(!is_string($this->errMsg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("allowStatus {$this->errMsg} must be string");
        }

        foreach($this->allowStatus as $dipCode){
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
            'SELECT state FROM diplomacy WHERE me = %i AND `state` IN %li LIMIT 1', 
            $this->nationID, 
            $this->allowStatus
        );
        if($state === null){
            return true;
        }
        $this->reason = $this->errMsg;
        return false;
    }
}