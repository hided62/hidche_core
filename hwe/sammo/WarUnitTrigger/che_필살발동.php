<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_필살발동 extends BaseWarUnitTrigger{
    static protected $priority = ObjectTrigger::PRIORITY_POST + 400;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('필살')){
            return true;
        }

        if($selfEnv['필살발동']??false){
            return true;
        }
        $selfEnv['필살발동'] = true;

        $oppose->getLogger()->pushGeneralBattleDetailLog("상대의 <R>필살</>공격!</>", ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog("<C>필살</>공격!</>", ActionLogger::PLAIN);

        $self->multiplyWarPowerMultiply($self->criticalDamage());

        return true;
    }
}