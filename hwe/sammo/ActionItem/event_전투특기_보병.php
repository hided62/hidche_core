<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;

class event_전투특기_보병 extends \sammo\BaseItem{

    protected $id = 50;
    protected $rawName = '비급';
    protected $name = '비급(보병)';
    protected $info = '[군사] 보병 계통 징·모병비 -10%<br>[전투] 공격 시 아군 피해 -10%, 수비 시 아군 피해 -20%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;


    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if(in_array($turnType, ['징병', '모병'])){
            if($varType == 'cost' && $aux['armType'] == GameUnitConst::T_FOOTMAN) return $value * 0.9;
        }
        
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->isAttacker()){
            return [1, 0.9];
        }
        return [1, 0.8];
    }
}