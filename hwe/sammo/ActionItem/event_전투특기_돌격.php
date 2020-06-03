<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use \sammo\BaseWarUnitTrigger;
use \sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\WarActivateSkills;

class event_전투특기_돌격 extends \sammo\BaseItem{

    protected $id = 60;
    protected $rawName = '비급';
    protected $name = '비급(돌격)';
    protected $info = '[전투] 상대 회피 불가, 공격 시 전투 페이즈 +1, 공격 시 대미지 +10%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'initWarPhase'){
            return $value + 1;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->isAttacker()){
            return [1.1, 1];
        }
        return [1, 1];
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            (new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '회피불가'))->setPriority(BaseWarUnitTrigger::PRIORITY_BEGIN + 200)
        );
    }
}