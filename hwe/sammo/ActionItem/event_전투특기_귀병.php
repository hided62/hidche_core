<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;

class event_전투특기_귀병 extends \sammo\BaseItem{

    protected $id = 40;
    protected $rawName = '비급';
    protected $name = '비급(귀병)';
    protected $info = '[군사] 귀병 계통 징·모병비 -10%<br>[전투] 계략 성공 확률 +20%p';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

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