<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;

class che_징병 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 72;
    protected $name = '징병';
    protected $info = '[군사] 징·모병비 -50%, 통솔 순수 능력치 보정 +15%';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_STRENGTH,
        SpecialityConst::STAT_INTEL
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost') return $value * 0.5;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'leadership'){
            return $value *= 1.15;
        }
        return $value;
    }
}