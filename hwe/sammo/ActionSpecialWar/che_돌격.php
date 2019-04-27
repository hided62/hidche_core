<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_돌격 implements iAction{
    use \sammo\DefaultAction;

    static $id = 60;
    static $name = '돌격';
    static $info = '[전투] 상대 회피 불가, 공격 시 전투 페이즈 +1, 공격 시 대미지 +10%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'initWarPhase'){
            return $value += 1;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->isAttacker()){
            return [1.1, 1];
        }
        return [1, 1];
    }
}