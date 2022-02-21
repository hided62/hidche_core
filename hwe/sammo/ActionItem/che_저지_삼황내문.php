<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use sammo\Util;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_저지발동;
use sammo\WarUnitTrigger\WarActivateSkills;
use sammo\WarUnitTriggerCaller;

class che_저지_삼황내문 extends \sammo\BaseItem{
    protected $rawName = '삼황내문';
    protected $name = '삼황내문(저지)';
    protected $info = '[전투] 수비 시 첫 페이즈 저지, 50% 확률로 2 페이즈 저지';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        if($unit->isAttacker()){
            return null;
        }
        if($unit->getPhase() >= 2){
            return null;
        }
        if($unit->getPhase() == 1 && Util::randBool(0.5)){
            return null;
        }

        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, true, '특수', '저지'),
            new che_저지발동($unit)
        );
    }
}