<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class che_징병_낙주 extends \sammo\BaseItem{

    protected $rawName = '낙주';
    protected $name = '낙주(징병)';
    protected $info = '[군사] 징·모병비 -50%, 통솔 순수 능력치 보정 +25%';
    protected $cost = 200;
    protected $consumable = false;

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