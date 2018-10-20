<?php

namespace sammo\Constraint;

class DifferentNationDestGeneral extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_DEST_GENERAL;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwException){
            return false;
        }

        if(!key_exists('nation', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in general");
        }

        if(!key_exists('nation', $this->destGeneral)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in dest general");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        if($this->destGeneral['nation'] != $this->general['nation']){
            return true;
        }

        $this->reason = "같은 국가의 장수입니다.";
        return false;
    }
}