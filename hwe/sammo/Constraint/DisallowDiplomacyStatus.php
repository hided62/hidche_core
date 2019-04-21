<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\DB;
class DisallowDiplomacyStatus extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARRAY_ARG;

    protected $nationID;
    protected $disallowStatus = [];

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(count($this->arg) != 2){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nationID, disallowStatus pair");
        }

        [$this->nationID, $this->disallowStatus] = $this->arg;
        if(!is_int($this->nationID)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("nationID {$this->nationID} must be int");
        }

        if(!is_array($this->disallowStatus)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("disallowStatus {$this->disallowStatus} must be array");
        }

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
            'SELECT state FROM diplomacy WHERE me = %i AND `state` IN %li LIMIT 1', 
            $this->nationID, 
            array_keys($this->disallowStatus)
        );
        if($state === null){
            return true;
        }
        $this->msg = $this->disallowStatus[$state];
        return false;
    }
}