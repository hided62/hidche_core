<?php

namespace sammo\Constraint;

class AllowStrategicCommand extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('war', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require war in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->nation['war'] == 0){
            return true;
        }

        $this->reason = "현재 전쟁 금지입니다.";
        return false;
    }
}