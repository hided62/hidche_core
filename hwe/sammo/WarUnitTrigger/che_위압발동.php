<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_위압발동 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 700;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('위압')){
            return true;
        }

        $oppose->getLogger()->pushGeneralBattleDetailLog('상대에게 <R>위압</>받았다!</>', ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog('상대에게 <C>위압</>을 줬다!</>', ActionLogger::PLAIN);
        $oppose->setWarPowerMultiply(0);

        return true;
    }
}