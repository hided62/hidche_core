<?php
namespace sammo\ActionItem;

use sammo\BaseWarUnitTrigger;
use \sammo\General;
use \sammo\WarUnit;
use sammo\WarUnitTrigger\che_부상무효;
use sammo\WarUnitTriggerCaller;

class event_전투특기_견고 extends \sammo\BaseItem{

    protected $rawName = '비급';
    protected $name = '비급(견고)';
    protected $info = '[전투] 상대 필살 확률 -20%p, 상대 계략 시도시 성공 확률 -10%p, 부상 없음, 아군 피해 -10%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 3000;

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        $debuff = [
            'warMagicSuccessProb' => 0.1,
            'warCriticalRatio' => 0.20,
        ][$statName] ?? 0;
        return $value - $debuff;
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_NONE + BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE * 404),
        );
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_NONE + BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE * 404),
        );
    }

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        return [1, 0.9];
    }
}