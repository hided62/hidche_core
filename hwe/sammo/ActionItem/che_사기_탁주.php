<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\BaseWarUnitTrigger;

class che_사기_탁주 extends \sammo\BaseItem{

    protected static $id = 3;
    protected static $name = '탁주(사기)';
    protected static $info = '[전투] 사기 +6. 1회용';
    protected static $cost = 1000;
    protected static $consumable = true;
    protected static $buyable = true;
    protected static $reqSecu = 1000;

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new 능력치변경($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM, 'atmos', '+', 6)
        );
    }
}