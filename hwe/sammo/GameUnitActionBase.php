<?php
namespace sammo;

class GameUnitActionBase implements iAction{
    use DefaultAction;


    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1, 1];
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller([new GameUnitInitTriggerBase($unit)]);
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller([new GameUnitPhaseTriggerBase($unit)]);
    }
}