<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;

class RemainCityCapacity extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY|Constraint::REQ_ARRAY_ARG;

    protected $key;
    protected $maxKey;
    protected $keyNick;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        [$this->key, $this->keyNick] = $this->arg;
        $this->maxKey = $this->key.'_max';

        if(!key_exists($this->key, $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in city");
        }

        if(!key_exists($this->maxKey, $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->maxKey} in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;
        $keyNick = $this->keyNick;

        if($this->city[$this->key] < $this->city[$this->maxKey]){
            return true;
        }

        $josaUn = JosaUtil::pick($keyNick, '은');
        $this->reason = "{$keyNick}{$josaUn} 충분합니다.";
        return false;
    }
}