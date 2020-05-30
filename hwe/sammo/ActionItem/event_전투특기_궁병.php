<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;

use \sammo\GameUnitConst;

class event_전투특기_궁병 extends \sammo\BaseItem{

    protected $id = 51;
    protected $rawName = '비급';
    protected $name = '비급(궁병)';
    protected $info = '[군사] 궁병 계통 징·모병비 -10%<br>[전투] 회피 확률 +20%p';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

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