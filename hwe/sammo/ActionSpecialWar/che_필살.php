<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_필살 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 71;
    protected $name = '필살';
    protected $info = '[전투] 필살 확률 +20%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_STRENGTH,
        SpecialityConst::STAT_INTEL
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio'){
            return $value + 0.2;
        }
        return $value;
    }
}