<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_event_집중 extends \sammo\BaseSpecial{

    protected $id = 43;
    protected $name = '집중';
    protected $info = '[전투] 계략 성공 시 대미지 +50%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_INTEL,
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessDamage'){
            return $value * 1.5;
        }
        return $value;
    }
}