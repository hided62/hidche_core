<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_저지발동;
use sammo\WarUnitTrigger\WarActivateSkills;
use sammo\WarUnitTriggerCaller;

class che_저지_상황내문 extends \sammo\BaseItem{

    protected $rawName = '상황내문';
    protected $name = '상황내문(저지)';
    protected $info = '[전투] 수비 시 저지';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        if($unit->getPhase() != 0){
            return null;
        }

        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, true, '특수', '저지'),
            new che_저지발동($unit)
        );
    }
}