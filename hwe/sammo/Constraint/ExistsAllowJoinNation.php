<?php

namespace sammo\Constraint;
use \sammo\DB;
use \sammo\GameConst;
use \sammo\Json;

class ExistsAllowJoinNation extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL|Constraint::REQ_ARRAY_ARG;

    protected $relYear;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('auxVar', $this->general)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require auxVar in general");
        }

        if(!is_int($this->arg[0])){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("first arg must be int(relYear)");
        }

        if(!is_array($this->arg[1])){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("first arg must be array(exludeList)");
        }

        $this->relYear = $this->arg;

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $db = DB::db();

        $relYear = $this->arg[0];
        $notIn = $this->arg[1];
        //이걸 호출하는 경우 분명 동일한 쿼리를 한번 더 부를 것. 쿼리 캐시를 기대함
        $nations = $db->queryFirstColumn(
            'SELECT nation, name, gennum, scout FROM nation WHERE scout=0 AND gennum < %i AND no NOT IN %li',
            $relYear<3?GameConst::$initialNationGenLimit:GameConst::$defaultMaxGeneral,
            $notIn
        );

        if($nations){
            return true;
        }

        $this->reason = "임관할 국가가 없습니다.";
        return false;
    }
}