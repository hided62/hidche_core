<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\BaseWarUnitTrigger;
use \sammo\WarUnit;
use \sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\능력치변경;

class che_훈련_청주 extends \sammo\BaseItem{

    protected $id = 4;
    protected $rawName = '청주';
    protected $name = '청주(훈련)';
    protected $info = '[전투] 훈련 +6. 1회용';
    protected $cost = 1000;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 1000;

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new 능력치변경($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM, 'train', '+', 6)
        );
    }
}