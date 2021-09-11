<?php

namespace sammo\ActionSpecialDomestic;

use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\BaseWarUnitTrigger;
use \sammo\WarUnitTrigger\WarActivateSkills;
use \sammo\WarUnitTrigger\che_반계시도;
use \sammo\WarUnitTrigger\che_반계발동;

class che_event_반계 extends \sammo\BaseSpecial
{

    protected $id = 45;
    protected $name = '반계';
    protected $info = '[전투] 상대의 계략 성공 확률 -10%p, 상대의 계략을 40% 확률로 되돌림, 반목 성공시 대미지 추가(+60% → +150%)';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_INTEL,
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        if ($statName === 'warMagicSuccessDamage' && $aux === '반목') {
            return $value + 0.9;
        }
        return $value;
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        $debuff = [
            'warMagicSuccessProb' => 0.1,
        ][$statName] ?? 0;
        return $value - $debuff;
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_반계시도($unit),
            new che_반계발동($unit)
        );
    }
}
