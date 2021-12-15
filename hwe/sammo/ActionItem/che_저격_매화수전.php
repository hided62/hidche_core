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

class che_저격_매화수전 extends \sammo\BaseItem{

    protected $id = 70;
    protected $rawName = '매화수전';
    protected $name = '매화수전(저격)';
    protected $info = '[전투] 새로운 상대와 전투 시 50% 확률로 저격 발동, 성공 시 사기+20';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, che_저격시도::TYPE_ITEM+BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*304, 0.5, 20, 40),
            new che_저격발동($unit, che_저격발동::TYPE_ITEM)
        );
    }
}