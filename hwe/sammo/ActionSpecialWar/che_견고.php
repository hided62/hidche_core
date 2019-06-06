<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitTrigger\WarActivateSkills;

class che_견고 implements iAction{
    use \sammo\DefaultAction;

    static $id = 62;
    static $name = '견고';
    static $info = '[전투] 상대 필살 불가, 상대 계략 시도시 성공 확률 -10%p';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER
    ];

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '필살불가', '위압불가', '격노불가', '계략약화')
        );
    }
}