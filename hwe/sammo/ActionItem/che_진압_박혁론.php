<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_저지발동;
use sammo\WarUnitTrigger\WarActivateSkills;
use sammo\WarUnitTriggerCaller;

class che_진압_박혁론 extends \sammo\BaseItem{

    protected $rawName = '박혁론';
    protected $name = '박혁론(진압)';
    protected $info = '[전투] 상대의 계략 되돌림, 격노 불가';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '반계불가', '격노불가'),
        );
    }
}