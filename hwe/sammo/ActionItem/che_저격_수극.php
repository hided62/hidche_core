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

    protected $rawName = '수극';
    protected $name = '수극(저격)';
    protected $info = '[전투] 전투 개시 시 저격. 1회용';
    protected $cost = 1000;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 1000;

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM, 1, 20, 40),
            new che_저격발동($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM)
        );
    }

    function tryConsumeNow(General $general, string $actionType, string $command):bool{
        if($actionType == 'GeneralTrigger' && $command == 'che_아이템치료'){
            return true;
        }
        return false;
    }
}