<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\WarUnitTriggerCaller;
use \sammo\WarUnit;
use \sammo\WarUnitTrigger\che_저격시도;
use \sammo\WarUnitTrigger\che_저격발동;
use \sammo\BaseWarUnitTrigger;

class che_저격_수극 extends \sammo\BaseItem{

    protected static $id = 2;
    protected static $name = '수극(저격)';
    protected static $info = '[전투] 전투 개시 전 20% 확률로 저격 시도. 1회용';
    protected static $cost = 1000;
    protected static $consumable = true;
    protected static $buyable = true;
    protected static $reqSecu = 1000;

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM, 0.2, 20, 40),
            new che_저격발동($unit)
        );
    }
}