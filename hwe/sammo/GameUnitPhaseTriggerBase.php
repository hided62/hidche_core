<?php
namespace sammo;

class GameUnitPhaseTriggerBase extends ObjectTrigger{
    static protected $priority = 10000;

    public function __construct(WarUnit $unit){
        $this->object = $unit;
        $this->unitType = $unit->getCrewType();
    }

    public function action(?array $env=null, $arg=null):?array{
        /** @var WarUnitGeneral $attacker */
        /** @var WarUnit $defender */
        [$attacker, $defender] = $arg;

        $attackerCrewType = $attacker->getCrewType();
        $defenderCrewType = $defender->getCrewType();

        //TODO: 목우
        return $env;
    }
}