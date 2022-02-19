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

class event_전투특기_반계 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(반계)';
    protected $info = '[전투] 상대의 계략 성공 확률 -10%p, 상대의 계략을 40% 확률로 되돌림, 반목 성공시 대미지 추가(+60% → +150%)';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessDamage' && $aux === '반목'){
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

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_반계시도($unit, BaseWarUnitTrigger::TYPE_ITEM),
            new che_반계발동($unit)
        );
    }
}