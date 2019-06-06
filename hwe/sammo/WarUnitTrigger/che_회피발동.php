<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_회피발동 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 500;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('회피')){
            return true;
        }

        $oppose->getLogger()->pushGeneralBattleDetailLog("상대가 <R>회피</>했다!</>", ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog("<C>회피</>했다!</>", ActionLogger::PLAIN);

        $oppose->multiplyWarPowerMultiply(0.2);

        return true;
    }
}