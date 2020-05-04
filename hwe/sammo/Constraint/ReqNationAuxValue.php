<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\Util;
use \sammo\Json;

/**
 * 범용으로 사용 가능한 국가 Aux 변수 검사도구
 */
class ReqNationAuxValue extends Constraint
{
    const REQ_VALUES = Constraint::REQ_NATION | Constraint::REQ_ARRAY_ARG;

    protected $key;
    protected $defaultValue;
    protected $reqVal;
    protected $comp;
    protected $errMsg;
    protected $auxVal;

    public function checkInputValues(bool $throwExeception = true): bool
    {
        if (!parent::checkInputValues($throwExeception) && !$throwExeception) {
            return false;
        }

        if (count($this->arg) == 5) {
            [$this->key, $this->defaultValue, $comp, $this->reqVal, $this->errMsg] = $this->arg;

            if (!in_array($comp, ['>', '>=', '==', '<=', '<', '!=', '===', '!=='])) {
                if (!$throwExeception) {
                    return false;
                }
                throw new \InvalidArgumentException("invalid comparator");
            }
        } else {
            if (!$throwExeception) {
                return false;
            }
            throw new \InvalidArgumentException("require key, defaultValue, comp, reqVal, errMsg");
        }

        $this->comp = $comp;

        $this->maxKey = $this->key . '_max';

        if (!key_exists('aux', $this->nation)) {
            if (!$throwExeception) {
                return false;
            }
            throw new \InvalidArgumentException("require aux in nation");
        }

        if ($this->nation['aux'] === null) {
            if (!$throwExeception) {
                return false;
            }
            throw new \InvalidArgumentException("invalid aux in nation(null)");
        }

        if (is_array($this->nation['aux'])) {
            $this->auxVal = $this->nation['aux'];
        } else if (is_string($this->nation['aux'])) {
            $this->auxVal = Json::decode($this->nation['aux']);
        } else {
            if (!$throwExeception) {
                return false;
            }
            throw new \InvalidArgumentException("inavlid aux in nation(type)");
        }


        if (!key_exists($this->key, $this->auxVal) && $this->defaultValue === null) {
            if (!$throwExeception) {
                return false;
            }
            throw new \InvalidArgumentException("require {$this->key} in nation['aux']");
        }

        if (!is_string($this->errMsg)) {
            if (!$throwExeception) {
                return false;
            }
            throw new \InvalidArgumentException("{$this->errMsg} must be string");
        }

        return true;
    }

    public function test(): bool
    {
        $this->checkInputValues();
        $this->tested = true;

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
        $result = ($comp)($this->auxVal[$this->key]??$this->defaultValue, $this->reqVal);

        if ($result === true) {
            return true;
        }

        if($result === true){
            return true;
        }

        $this->reason = $this->errMsg;
        
        return false;
    }
}
