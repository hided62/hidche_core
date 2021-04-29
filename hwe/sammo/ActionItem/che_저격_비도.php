<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_저격시도;
use \sammo\WarUnitTrigger\che_저격발동;

class che_저격_비도 extends \sammo\BaseItem{

    protected $id = 70;
    protected $rawName = '비도';
    protected $name = '비도(저격)';
    protected $info = '[전투] 새로운 상대와 전투 시 50% 확률로 저격 발동, 성공 시 사기+10';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, che_저격시도::TYPE_ITEM+BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*305, 0.5, 20, 40),
            new che_저격발동($unit, che_저격발동::TYPE_ITEM)
        );
    }
}