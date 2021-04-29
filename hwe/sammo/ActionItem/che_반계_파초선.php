<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\BaseWarUnitTrigger;
use \sammo\WarUnitTrigger\WarActivateSkills;
use \sammo\WarUnitTrigger\che_반계시도;
use \sammo\WarUnitTrigger\che_반계발동;

class che_반계_파초선 extends \sammo\BaseItem{

    protected $id = 45;
    protected $rawName = '파초선';
    protected $name = '파초선(반계)';
    protected $info = '[전투] 상대의 계략 성공 확률 -10%p, 상대의 계략을 40% 확률로 되돌림';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_ITEM +BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*302, false, '계략약화'),
            new che_반계시도($unit, BaseWarUnitTrigger::TYPE_ITEM +BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*302),
            new che_반계발동($unit)
        );
    }
}