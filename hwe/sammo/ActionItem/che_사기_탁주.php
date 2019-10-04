<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\BaseWarUnitTrigger;

class che_사기_탁주 extends \sammo\BaseItem{

    protected $id = 3;
    protected $name = '탁주(사기)';
    protected $info = '[전투] 사기 +6. 1회용';
    protected $cost = 1000;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 1000;

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new 능력치변경($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM, 'atmos', '+', 6)
        );
    }
}