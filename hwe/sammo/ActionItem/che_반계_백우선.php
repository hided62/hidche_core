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

class che_반계_백우선 extends \sammo\BaseItem{

    protected $rawName = '백우선';
    protected $name = '백우선(반계)';
    protected $info = '[전투] 상대의 계략 성공 확률 -10%p, 상대의 계략을 30% 확률로 되돌림, 반목 성공시 대미지 추가(+60% → +100%), 소모 군량 +10%';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        $debuff = [
            'warMagicSuccessProb' => 0.1,
        ][$statName] ?? 0;
        return $value - $debuff;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'killRice'){
            return $value * 1.1;
        }
        if($statName === 'warMagicSuccessDamage' && $aux === '반목'){
            return $value + 0.4;
        }
        return $value;
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_반계시도($unit, BaseWarUnitTrigger::TYPE_ITEM +BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*301, 0.3),
            new che_반계발동($unit)
        );
    }
}