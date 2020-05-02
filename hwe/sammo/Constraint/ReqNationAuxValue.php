<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\Util;
use \sammo\Json;

/**
 * 범용으로 사용 가능한 국가 Aux 변수 검사도구
 */
class ReqNationAuxValue extends Constraint{
    const REQ_VALUES = Constraint::REQ_NATION|Constraint::REQ_ARRAY_ARG;

    protected $key;
    protected $maxKey;
    protected $keyNick;
    protected $reqVal;
    protected $comp;
    protected $errMsg;
    protected $auxVal;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(count($this->arg) == 5){
            [$this->key, $this->keyNick, $comp, $this->reqVal, $this->errMsg] = $this->arg;

            if(!in_array($comp, ['>', '>=', '==', '<=', '<', '!=', '===', '!=='])){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("invalid comparator");
            }
        }
        else{
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require key, keyNick, comp, reqVal[, errMsg]");
        }

        $this->comp = $comp;
        
        $this->maxKey = $this->key.'_max';

        if(!key_exists('aux', $this->nation)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require aux in nation");
        }

        if($this->nation['aux'] === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("invalid aux in nation(null)");
        }

        if(is_array($this->nation['aux'])){
            $this->auxVal = $this->nation['aux'];
        }
        else if(is_string($this->nation['aux'])){
            $this->auxVal = Json::decode($this->nation['aux']);
        }
        else{
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("inavlid aux in nation(type)");
        }


        if(!key_exists($this->key, $this->auxVal)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in nation['aux']");
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

            if(!key_exists($this->maxKey, $this->auxVal)){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("require {$this->maxKey} in nation['aux']");
            }
            $this->isPercent = true;
        }

        if($this->errMsg!==null && !is_string($this->errMsg)){
            if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("{$this->errMsg} must be string or null");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;
        $keyNick = $this->keyNick;

        if ($this->isPercent) {
            $reqVal = $this->auxVal[$this->maxKey] * $this->reqVal;
        }
        else{
            $reqVal = $this->reqVal;
        }

        $compList = [
            '<'=>function($target, $src){
                return ($target < $src)?true:'너무 많습니다.';
            },
            '<='=>function($target, $src){
                return ($target <= $src)?true:'너무 많습니다.';
            },
            '=='=>function($target, $src)use($keyNick){
                return ($target == $src)?true:"올바르지 않은 {$keyNick} 입니다.";
            },
            '!='=>function($target, $src)use($keyNick){
                return ($target != $src)?true:"올바르지 않은 {$keyNick} 입니다.";
            },
            '==='=>function($target, $src)use($keyNick){
                return ($target === $src)?true:"올바르지 않은 {$keyNick} 입니다.";
            },
            '!=='=>function($target, $src)use($keyNick){
                return ($target !== $src)?true:"올바르지 않은 {$keyNick} 입니다.";
            },
            '>='=>function($target, $src){
                if($target >= $src){
                    return true;
                }
                if($src == 1){
                    return '없습니다';
                }
                return '부족합니다.';
            },
            '>'=>function($target, $src){
                if($target > $src){
                    return true;
                }
                if($src == 0){
                    return '없습니다';
                }
                return '부족합니다.';
            },
        ];

        $comp = $compList[$this->comp];
        $result = ($comp)($this->auxVal[$this->key], $reqVal);

        if($result === true){
            return true;
        }

        if($this->errMsg){
            $this->reason = $this->errMsg;
        }
        else{
            $josaYi = JosaUtil::pick($keyNick, '이');
            $this->reason = "{$keyNick}{$josaYi} {$result}";
        }
        
        return false;
    }
}