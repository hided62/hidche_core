<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_방어력증가5p extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_FINAL + 200; //최후미

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->isAttacker()){
            $oppose->multiplyWarPowerMultiply(1/1.05);
        }

        return true;
    }
}