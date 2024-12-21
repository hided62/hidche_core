<?php

namespace sammo\Constraint;

use sammo\Enums\PenaltyKey;
use sammo\Json;

class NoPenalty extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_ARG|Constraint::REQ_BACKED_ENUM_ARG;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('penalty', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require penalty in general");
        }

        if(!($this->arg instanceof PenaltyKey)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require penalty key");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        /** @var PenaltyKey */
        $checkKey = $this->arg;

        $penaltyList = JSON::decode($this->general['penalty']);
        if(!key_exists($checkKey->value, $penaltyList)){
            return true;
        }

        $this->reason = "징계 사유: {$penaltyList[$checkKey->value]}";
        return false;
    }
}