<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;

class RemainCityTrust extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY|Constraint::REQ_STRING_ARG;

    protected $key;
    protected $maxKey;
    protected $maxVal;
    protected $keyNick;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        $this->keyNick = $this->arg;
        $this->key = 'trust';
        $this->maxVal = 100;

        if(!key_exists($this->key, $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;
        $keyNick = $this->keyNick;

        if($this->city[$this->key] < $this->maxVal){
            return true;
        }

        $josaUn = JosaUtil::pick($keyNick, '은');
        $this->reason = "{$keyNick}{$josaUn} 충분합니다.";
        return false;
    }
}