<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\Util;
use \sammo\DB;

/**
 * 범용으로 사용 가능한 환경 변수 검사도구
 */
class ReqEnvValue extends Constraint{
    const REQ_VALUES = Constraint::REQ_ARRAY_ARG;

    protected $key;
    protected $reqVal;
    protected $comp;
    protected $msg;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(count($this->arg) == 4){
            [$this->key, $comp, $this->reqVal, $this->msg] = $this->arg;

            if(!in_array($comp, ['>', '>=', '==', '<=', '<', '!=', '===', '!=='])){
                if(!$throwExeception){return false; }
                throw new \InvalidArgumentException("invalid comparator($comp)");
            }
        }
        else{
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require key, reqVal, comp, errmsg");
        }

        $this->comp = $comp;

        if(!key_exists($this->key, $this->env)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require {$this->key} in env");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $reqVal = $this->reqVal;
        

        $compList = [
            '<'=>function($target, $src){
                return ($target < $src);
            },
            '<='=>function($target, $src){
                return ($target <= $src);
            },
            '=='=>function($target, $src){
                return ($target == $src);
            },
            '!='=>function($target, $src){
                return ($target != $src);
            },
            '==='=>function($target, $src){
                return ($target === $src);
            },
            '!=='=>function($target, $src){
                return ($target !== $src);
            },
            '>='=>function($target, $src){
                return ($target >= $src);
            },
            '>'=>function($target, $src){
                return ($target > $src);
            },
        ];

        $comp = $compList[$this->comp];
        $result = ($comp)($this->env[$this->key], $reqVal);

        if($result === true){
            return true;
        }

        $this->reason = $this->msg;
        
        return false;
    }
}