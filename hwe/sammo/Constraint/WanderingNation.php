<?php

namespace sammo\Constraint;

class WanderingNation extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('level', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require level in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->nation['level'] == 0){
            return true;
        }

        $this->reason = "방랑군이어야 합니다";
        return false;
    }
}