<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_징병 extends \sammo\BaseSpecial{

    protected $id = 72;
    protected $name = '징병';
    protected $info = '[군사] 징·모병비 -50%, 통솔 순수 능력치 보정 +25%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost') return $value * 0.5;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'leadership'){
            return $value + $general->getVar('leadership') * 0.25;
        }
        return $value;
    }
}