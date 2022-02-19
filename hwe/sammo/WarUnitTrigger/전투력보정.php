<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;

class 전투력보정 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN;

    public function __construct(protected int|float $attackerWarPowerMultiplier, protected int|float $defenderWarPowerMultiplier = 1){
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        $self->multiplyWarPowerMultiply($this->attackerWarPowerMultiplier);
        $oppose->multiplyWarPowerMultiply($this->defenderWarPowerMultiplier);

        $this->processConsumableItem();

        return true;
    }
}