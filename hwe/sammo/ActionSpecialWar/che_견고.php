<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitTrigger\WarActivateSkills;

class che_견고 extends \sammo\BaseSpecial{

    protected $id = 62;
    protected $name = '견고';
    protected $info = '[전투] 상대 필살 불가, 상대 계략 시도시 성공 확률 -10%p';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '필살불가', '위압불가', '격노불가', '계략약화')
        );
    }
}