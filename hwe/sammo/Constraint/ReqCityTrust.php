<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;

class RemainCityTrust extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY|Constraint::REQ_NUMERIC_ARG;

    protected $key;
    protected $maxKey;
    protected $keyNick;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        $this->keyNick = '민심';
        $this->key = 'trust';
        $this->reqVal = $this->arg;

        if(!key_exists($this->key, $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->city[$this->key] >= $this->reqVal){
            return true;
        }

        $this->reason = "민심이 낮아 주민들이 도망갑니다.";
        return false;
    }
}