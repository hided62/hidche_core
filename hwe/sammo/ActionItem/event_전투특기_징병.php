<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_징병 extends \sammo\BaseItem{

    protected $id = 72;
    protected $rawName = '비급';
    protected $name = '비급(징병)';
    protected $info = '[군사] 징·모병비 -50%, 통솔 순수 능력치 보정 +15%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

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