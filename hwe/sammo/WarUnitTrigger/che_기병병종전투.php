<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_기병병종전투 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_FINAL + 100; //최후미

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->isAttacker()){
            $oppose->multiplyWarPowerMultiply(1/0.95);
            $self->multiplyWarPowerMultiply(0.95);
        }
        else if($oppose instanceof WarUnitCity){
            $self->multiplyWarPowerMultiply(0.9);
        }
        else{
            $oppose->multiplyWarPowerMultiply(0.97);
            $self->multiplyWarPowerMultiply(1.02);
        }


        return true;
    }
}