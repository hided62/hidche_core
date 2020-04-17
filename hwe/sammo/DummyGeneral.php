<?php

namespace sammo;
class DummyGeneral extends General{
    public function __construct(bool $initLogger=true){
        $raw = [
            'no'=>0,
            'name'=>'Dummy',
            'city'=>0,
            'nation'=>0,
            'officer_level'=>0,
            'crewtype'=>-1,
            'turntime'=>'2012-03-04 05:06:07.000000',
            'experience'=>0,
            'dedication'=>0,
            'gold'=>0,
            'rice'=>0,
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
        if($this->logger){
            $this->initLogger(1, 1);
        }
        return true;
    }
}