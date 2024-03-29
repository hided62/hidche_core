<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\che_필살강화_회피불가;

class event_전투특기_필살 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(필살)';
    protected $info = '[전투] 필살 확률 +30%p, 필살 발동시 대상 회피 불가, 필살 계수 향상';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio'){
            return $value + 0.3;
        }
        if($statName === 'criticalDamageRange'){
            [$rangeMin, $rangeMax] = $value;
            return [($rangeMin + $rangeMax) / 2, $rangeMax];
        }

        return $value;
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_필살강화_회피불가($unit)
        );
    }
}