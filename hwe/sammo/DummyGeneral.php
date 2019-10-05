<?php

namespace sammo;
class DummyGeneral extends General{
    public function __construct(bool $initLogger=true){
        $raw = [
            'no'=>0,
            'name'=>'Dummy',
            'city'=>0,
            'nation'=>0,
            'level'=>0,
            'crewtype'=>-1
        ];

        $this->raw = $raw;

        $this->resultTurn = new LastTurn();

        if($initLogger){
            $this->initLogger(1, 1);
        }
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller();
    }

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller();
    }

    function applyDB($db):bool{
        return true;
    }
}