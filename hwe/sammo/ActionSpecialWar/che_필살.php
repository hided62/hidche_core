<?php
namespace sammo\ActionSpecialWar;

use sammo\BaseWarUnitTrigger;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\che_필살강화_회피불가;
use sammo\WarUnitTrigger\WarActivateSkills;

class che_필살 extends \sammo\BaseSpecial{

    protected $id = 71;
    protected $name = '필살';
    protected $info = '[전투] 필살 확률 +30%p, 필살 발동시 대상 회피 불가, 필살 계수 향상';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio'){
            return $value + 0.30;
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