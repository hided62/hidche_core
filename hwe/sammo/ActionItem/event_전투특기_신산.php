<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_신산 extends \sammo\BaseItem{

    protected $id = 41;
    protected $rawName = '비급';
    protected $name = '비급(신산)';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +10%p<br>[전투] 계략 시도 확률 +20%p, 계략 성공 확률 +20%p';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.1;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicTrialProb'){
            return $value + 0.2;
        }
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.2;
        }
        return $value;
    }
}