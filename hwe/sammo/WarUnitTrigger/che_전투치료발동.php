<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;
use sammo\ActionLogger;

class che_전투치료발동 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 300;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('치료')){
            return true;
        }

        if($selfEnv['치료발동']??false){
            return true;
        }
        $selfEnv['치료발동'] = true;

        $oppose->getLogger()->pushGeneralBattleDetailLog("상대가 <R>치료</>했다!", ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog("<C>치료</>했다!", ActionLogger::PLAIN);

        $oppose->multiplyWarPowerMultiply(0.7);
        $self->getGeneral()->setVar('injury', 0);

        $this->processConsumableItem();

        return true;
    }
}