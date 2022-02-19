<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use \sammo\BaseWarUnitTrigger;
use \sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\WarActivateSkills;
use \sammo\WarUnitTrigger\che_돌격지속;

class event_전투특기_돌격 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(돌격)';
    protected $info = '[전투] 공격 시 대등/유리한 병종에게는 퇴각 전까지 전투, 공격 시 페이즈 + 2, 공격 시 대미지 +5%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

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