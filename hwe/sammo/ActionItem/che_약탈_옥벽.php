<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;
use sammo\WarUnitTrigger\che_약탈발동;
use sammo\WarUnitTrigger\che_약탈시도;
use sammo\WarUnitTriggerCaller;

class che_약탈_옥벽 extends \sammo\BaseItem{

    protected $rawName = '옥벽';
    protected $name = '옥벽(약탈)';
    protected $info = '[전투] 새로운 상대와 전투 시 10% 확률로 상대 금, 쌀 10% 약탈';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_약탈시도($unit, che_약탈시도::TYPE_ITEM, 0.1, 0.1),
            new che_약탈발동($unit, che_약탈발동::TYPE_ITEM)
        );
    }
}
