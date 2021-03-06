<?php

namespace sammo\Constraint;

class MustBeNPC extends Constraint{

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('npc', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require npc in general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->general['npc'] < 2){
            $this->reason = "NPC여야 합니다.";
            return false;
        }
        return true;
    }
}