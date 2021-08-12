<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\Util;

class ReqCityCapacity extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY|Constraint::REQ_ARRAY_ARG;

    protected $key;
    protected $maxKey;
    protected $keyNick;
    protected $reqVal;
    protected $isPercent;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        [$this->key, $this->keyNick, $this->reqVal] = $this->arg;
        
        $this->maxKey = $this->key.'_max';

        if(!key_exists($this->key, $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in city");
        }

        if(is_numeric($this->reqVal)){
            $this->isPercent = false;
        }
        else if(is_string($this->reqVal)){
            $this->reqVal = Util::convPercentStrToFloat($this->reqVal);
            if($this->reqVal === null){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require valid reqVal(percentStr|numeric) format");
            }

            if(!key_exists($this->maxKey, $this->city)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require {$this->maxKey} in city");
            }
            $this->isPercent = true;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;
        $keyNick = $this->keyNick;

        if($this->isPercent){
            if($this->city[$this->key] >= $this->city[$this->maxKey] * $this->reqVal){
                return true;
            }
            
        }
        else{
            if($this->city[$this->key] >= $this->reqVal){
                return true;
            }
        }

        $josaYi = JosaUtil::pick($keyNick, '이');
        $this->reason = "{$keyNick}{$josaYi} 부족합니다.";
        return false;
    }
}