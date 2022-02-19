<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\che_격노시도;
use sammo\WarUnitTrigger\che_격노발동;

class che_격노_구정신단경 extends \sammo\BaseItem{

    protected $rawName = '구정신단경';
    protected $name = '구정신단경(격노)';
    protected $info = '[전투] 상대방 필살 시 격노(필살) 발동, 회피 시도시 25% 확률로 격노 발동, 공격 시 일정 확률로 진노(1페이즈 추가), 격노마다 대미지 20% 추가 중첩';
    protected $cost = 200;
    protected $consumable = false;

    public function getWarPowerMultiplier(WarUnit $unit):array{
        $activatedCnt = $unit->hasActivatedSkillOnLog('격노');
        return [1 + 0.2*$activatedCnt, 1];
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_격노시도($unit, che_격노시도::TYPE_ITEM),
            new che_격노발동($unit)
        );
    }
}