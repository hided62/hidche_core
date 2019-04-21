<?php

namespace sammo\Constraint;

class ConstructableCity extends Constraint{
    const REQ_VALUES = Constraint::REQ_CITY;

    protected $relYear;

    public function checkInputValues(bool $throwExeception=true){
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('nation', $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in city");
        }

        if(!key_exists('level', $this->city)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require nation in city");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $city = $this->city;

        if($city['nation'] != 0){
            $this->reason = '공백지가 아닙니다.';
            return false;
        }

        if(!in_array($city['level'], [5, 6])){
            $this->reason = '중, 소 도시에만 가능합니다.';
            return false;
        }

        return true;
    }
}