<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use \sammo\BaseWarUnitTrigger;
use \sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\WarActivateSkills;
use \sammo\WarUnitTrigger\che_돌격지속;

class che_돌격 extends \sammo\BaseSpecial{

    protected $id = 60;
    protected $name = '돌격';
    protected $info = '[전투] 공격 시 대등/유리한 병종에게는 퇴각 전까지 전투, 공격 시 페이즈 + 2, 공격 시 대미지 +5%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'initWarPhase'){
            return $value + 2;
        }
        return $value;
    }
    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->isAttacker()){
            return [1.05, 1];
        }
        return [1, 1];
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_돌격지속($unit)
        );
    }
}