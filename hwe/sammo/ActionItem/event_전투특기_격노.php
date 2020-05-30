<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\che_격노시도;
use sammo\WarUnitTrigger\che_격노발동;

class event_전투특기_격노 extends \sammo\BaseItem{

    protected $id = 74;
    protected $rawName = '비급';
    protected $name = '비급(격노)';
    protected $info = '[전투] 상대방 필살 및 회피 시도시 일정 확률로 격노(필살) 발동, 공격 시 일정 확률로 진노(1페이즈 추가)';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_격노시도($unit, \sammo\BaseWarUnitTrigger::TYPE_ITEM),
            new che_격노발동($unit)
        );
    }
}