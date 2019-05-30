<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\GameUnitConst;
use \sammo\WarUnit;

class che_귀병 implements iAction{
    use \sammo\DefaultAction;

    static $id = 40;
    static $name = '귀병';
    static $info = '[군사] 귀병 계통 징·모병비 -10%<br>[전투] 계략 성공 확률 +20%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_INTEL | SpecialityConst::ARMY_WIZARD | SpecialityConst::REQ_DEXTERITY
    ];

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost' && $aux['armType'] == GameUnitConst::T_WIZARD) return $value * 0.9;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.2;
        }
        return $value;
    }
}