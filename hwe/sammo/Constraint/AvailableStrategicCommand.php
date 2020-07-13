<?php

namespace sammo\Constraint;

class AvailableStrategicCommand extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION | Constraint::REQ_INT_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('strategic_cmd_limit', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require strategic_cmd_limit in nation");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->nation['strategic_cmd_limit'] <= $this->arg){
            return true;
        }

        $this->reason = "전략기한이 남았습니다.";
        return false;
    }
}