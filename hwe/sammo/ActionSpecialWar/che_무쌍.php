<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_무쌍 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 61;
    protected $name = '무쌍';
    protected $info = '[전투] 대미지 +10%, 공격 시 필살 확률 +10%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_STRENGTH
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio' && $aux['isAttacker']??false){
            return $value += 0.1;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1.1, 1];
    }
}