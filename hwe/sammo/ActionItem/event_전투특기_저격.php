<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_저격시도;
use \sammo\WarUnitTrigger\che_저격발동;

class event_전투특기_저격 extends \sammo\BaseItem{

    protected $id = 70;
    protected $rawName = '비급';
    protected $name = '비급(저격)';
    protected $info = '[전투] 새로운 상대와 전투 시 1/3 확률로 저격 발동, 성공 시 사기+10';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, che_저격시도::TYPE_ITEM, 1/3, 20, 60),
            new che_저격발동($unit, che_저격발동::TYPE_ITEM)
        );
    }
}