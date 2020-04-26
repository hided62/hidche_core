<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;

use \sammo\GameUnitConst;

class che_궁병 extends \sammo\BaseSpecial{

    protected $id = 51;
    protected $name = '궁병';
    protected $info = '[군사] 궁병 계통 징·모병비 -10%<br>[전투] 회피 확률 +20%p';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP | SpecialityHelper::REQ_DEXTERITY | SpecialityHelper::ARMY_ARCHER,
        SpecialityHelper::STAT_STRENGTH | SpecialityHelper::REQ_DEXTERITY | SpecialityHelper::ARMY_ARCHER
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost' && $aux['armType'] == GameUnitConst::T_ARCHER) return $value * 0.9;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warAvoidRatio'){
            return $value + 0.2;
        }
        return $value;
    }
}