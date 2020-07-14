<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_위압시도;
use \sammo\WarUnitTrigger\che_위압발동;

class event_전투특기_위압 extends \sammo\BaseItem{

    protected $id = 63;
    protected $rawName = '비급';
    protected $name = '비급(위압)';
    protected $info = '[전투] 첫 페이즈 위압 발동(적 공격, 회피 불가, 사기 5 감소)';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        if($unit->getPhase() != 0){
            return null;
        }
        return new WarUnitTriggerCaller(
            new che_위압시도($unit),
            new che_위압발동($unit)
        );
    }
}