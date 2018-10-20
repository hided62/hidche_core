<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\Util;

class RegGeneralValue extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_ARRAY_ARG;

    protected $key;
    protected $maxKey;
    protected $keyNick;
    protected $reqVal;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        [$this->key, $this->keyNick, $this->reqVal] = $this->arg;
        
        $this->maxKey = $this->key.'2';

        if(!key_exists($this->key, $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in city");
        }

        if(is_numeric($this->reqVal)){
            $this->isPercent = false;
        }
        else if(is_str($this->reqVal)){
            $this->reqVal = Util::convPercentStrToFloat($this->reqVal);
            if($this->reqVal === null){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require valid reqVal(percentStr|numeric) format");
            }

            if(!key_exists($this->maxKey, $this->city)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require {$this->maxKey} in general");
            }
            $this->isPercent = true;
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->isPercent){
            if($this->general[$this->key] >= $this->general[$this->maxKey] * $this->reqVal){
                return true;
            }
            
        }
        else{
            if($this->general[$this->key] >= $this->reqVal){
                return true;
            }
        }

        if($this->reqVal === 1){
            $josaYi = JosaUtil::pick($keyNick, '이');
            $this->reason = "{$keyNick}{$josaUn} 없습니다.";
        }
        else{
            $josaYi = JosaUtil::pick($keyNick, '이');
            $this->reason = "{$keyNick}{$josaUn} 부족합니다.";
        }
        
        return false;
    }
}