<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_위압시도;
use \sammo\WarUnitTrigger\che_위압발동;

class che_위압_조목삭 extends \sammo\BaseItem{

    protected $rawName = '조목삭';
    protected $name = '조목삭(위압)';
    protected $info = '[전투] 첫 페이즈 위압 발동(적 공격, 회피 불가, 사기 5 감소)';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_위압시도($unit),
            new che_위압발동($unit)
        );
    }
}