<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_집중 implements iAction{
    use \sammo\DefaultAction;

    static $id = 43;
    static $name = '집중';
    static $info = '[전투] 계략 성공 시 대미지 +50%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_INTEL,
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessDamage'){
            return $value * 1.5;
        }
        return $value;
    }
}