<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\GameUnitConst;
use \sammo\WarUnit;
use \sammo\WarUnitCity;
use sammo\RandUtil;
use \sammo\WarUnitTriggerCaller;
use \sammo\BaseWarUnitTrigger;
use sammo\WarUnitTrigger\event_충차아이템소모;

class event_충차 extends \sammo\BaseItem{

    protected $rawName = '충차';
    protected $name = '충차';
    protected $info = '[전투] 성벽 공격 시 대미지 +50%, 2회용';
    protected $cost = 2000;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 3000;


    const REMAIN_KEY = 'remain충차';

    function onArbitraryAction(General $general, RandUtil $rng, string $actionType, ?string $phase = null, $aux = null): ?array
    {
        if($actionType != '장비매매'){
            return $aux;
        }
        if($phase != '구매'){
            return $aux;
        }

        $general->setAuxVar(static::REMAIN_KEY, 2);
        return $aux;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        if($unit->getOppose() instanceof WarUnitCity){
            return [1.5, 1];
        }
        return [1, 1];
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new event_충차아이템소모($unit, BaseWarUnitTrigger::TYPE_CONSUMABLE_ITEM)
        );
    }
}