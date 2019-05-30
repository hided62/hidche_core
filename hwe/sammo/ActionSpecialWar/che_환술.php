<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_환술 implements iAction{
    use \sammo\DefaultAction;

    static $id = 42;
    static $name = '환술';
    static $info = '[전투] 계략 성공 확률 +10%p, 계략 성공 시 대미지 +30%';

    static $selectWeightType = SpecialityConst::WEIGHT_PERCENT;
    static $selectWeight = 5;
    static $type = [
        SpecialityConst::STAT_INTEL,
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.1;
        }
        if($statName === 'warMagicSuccessDamage'){
            return $value * 1.3;
        }
        return $value;
    }
}