<?php

namespace sammo\Constraint;

use \sammo\JosaUtil;
use \sammo\Util;
use \sammo\DB;
use \sammo\MustNotBeReachedException;

class AllowRebellion extends Constraint{
    const REQ_VALUES = Constraint::REQ_GENERAL;

    public function checkInputValues(bool $throwExeception=true):bool{
        if(!parent::checkInputValues($throwExeception) && !$throwExeception){
            return false;
        }

        if(!key_exists('killturn', $this->env)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException("require killturn in env");
        }

        return true;
    }

    public function test():bool{
        $this->checkInputValues();
        $this->tested = true;

        $general = $this->general;
        $nationID = $general['nation'];

        if($nationID == 0){
            throw new MustNotBeReachedException('재야임');
        }

        $db = DB::db();
        $lord = $db->queryFirstRow('SELECT no, killturn, npc FROM general WHERE nation = %i AND officer_level = 12', $nationID);

        if(!$lord){
            throw new MustNotBeReachedException('군주가 없음');
        }

        if($lord['no'] == $general['no']){
            $this->reason = '이미 군주입니다.';
            return false;
        }

        if($lord['killturn'] >= $this->env['killturn']){
            $this->reason = '군주가 활동중입니다.';
            return false;
        }

        if(in_array($lord['npc'], [2,3,6,9])){
            $this->reason = '군주가 NPC입니다.';
            return false;
        }

        return true;
    }
}