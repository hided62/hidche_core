<?php

namespace sammo\Constraint;
use \sammo\DB;
use \sammo\GameConst;

class AllowJoinDestNation extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_NATION|Constraint::REQ_INT_ARG;

    protected $relYear;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('auxVar', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require auxVar in general");
        }

        if(!key_exists('scout', $this->destNation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require scout in nation");
        }

        if(!key_exists('gennum', $this->destNation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require gennum in nation");
        }

        $this->relYear = $this->arg;

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();

        if($this->relYear < GameConst::$openingPartYear && $this->destNation['gennum'] >= GameConst::$initialNationGenLimit){
            $this->reason = "임관이 제한되고 있습니다.";
            return false;
        }

        if($this->destNation['scout'] == 1){
            $this->reason = "임관이 금지되어 있습니다.";
            return false;
        }

        $joinedNations = $this->general['auxVar']['joinedNations']??[];
        if(in_array($this->destNation['nation'], $joinedNations)){
            $this->reason = "이미 임관했었던 국가입니다.";
            return false;
        }

        return true;
    }
}