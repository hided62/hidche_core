<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_무쌍 extends \sammo\BaseSpecial{

    protected $id = 61;
    protected $name = '무쌍';
    protected $info = '[전투] 대미지 +10%, 공격 시 필살 확률 +10%p';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
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