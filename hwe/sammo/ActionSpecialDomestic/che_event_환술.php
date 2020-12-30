<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_event_환술 extends \sammo\BaseSpecial{

    protected $id = 42;
    protected $name = '환술';
    protected $info = '[전투] 계략 성공 확률 +10%p, 계략 성공 시 대미지 +30%';

    static $selectWeightType = SpecialityHelper::WEIGHT_PERCENT;
    static $selectWeight = 5;
    static $type = [
        SpecialityHelper::STAT_INTEL,
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