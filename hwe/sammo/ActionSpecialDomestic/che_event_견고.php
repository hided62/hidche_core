<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitTrigger\che_부상무효;
use sammo\WarUnitTrigger\WarActivateSkills;

class che_event_견고 extends \sammo\BaseSpecial{

    protected $id = 62;
    protected $name = '견고';
    protected $info = '[전투] 상대 필살, 저격 불가, 상대 계략 시도시 성공 확률 -10%p, 부상 없음, 아군 피해 -10%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_NONE),
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '저격불가')
        );
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '필살불가', '계략약화', '저격불가')
        );
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1, 0.9];
    }
}