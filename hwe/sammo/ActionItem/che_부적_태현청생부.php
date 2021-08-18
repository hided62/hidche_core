<?php

namespace sammo\ActionItem;

use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitTrigger\che_부상무효;
use sammo\WarUnitTrigger\WarActivateSkills;

class che_부적_태현청생부 extends \sammo\BaseItem
{

    protected $id = 9;
    protected $rawName = '태현청생부';
    protected $name = '태현청생부(부적)';
    protected $info = '[전투] 저격 불가, 부상 없음';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattleInitSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_ITEM + BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE * 303),
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_ITEM + BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE * 303, false, '저격불가')
        );
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_ITEM + BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE * 303, false, '저격불가')
        );
    }
}
