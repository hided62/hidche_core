<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_훈련_청주 extends \sammo\BaseItem{

    protected static $id = 4;
    protected static $name = '청주(훈련)';
    protected static $info = '[전투] 훈련 +3. 1회용';
    protected static $cost = 1000;
    protected static $consumable = true;
    protected static $buyable = true;

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller([
            new 능력치변경($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM, 'train', '+', 3),
        ]);
    }
}