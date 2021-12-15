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
    protected $info = '[전투] 상대 필살 확률 -20%p, 상대 계략 시도시 성공 확률 -10%p, 부상 없음, 아군 피해 -10%';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        $debuff = [
            'warMagicSuccessProb' => 0.1,
            'warCriticalRatio' => 0.20,
        ][$statName] ?? 0;
        return $value - $debuff;
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_ITEM),
        );
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_ITEM),
        );
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1, 0.9];
    }
}