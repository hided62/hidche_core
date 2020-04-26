<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\che_격노시도;
use sammo\WarUnitTrigger\che_격노발동;

class che_격노 extends \sammo\BaseSpecial{

    protected $id = 74;
    protected $name = '격노';
    protected $info = '[전투] 상대방 필살 및 회피 시도시 일정 확률로 격노(필살) 발동, 공격 시 일정 확률로 진노(1페이즈 추가)';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_격노시도($unit),
            new che_격노발동($unit)
        );
    }
}