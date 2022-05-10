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

class che_저지발동 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST; //최우선 순위

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('저지')){
            return true;
        }

        if($selfEnv['저지발동']??false){
            return true;
        }
        $selfEnv['저지발동'] = true;

        $self->getLogger()->pushGeneralBattleDetailLog("상대를 <C>저지</>했다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("저지</>당했다!", ActionLogger::PLAIN);

        $self->getGeneral()->addDex($oppose->getCrewType(), $oppose->getWarPower() * 0.9);
        $self->getGeneral()->addDex($self->getCrewType(), $self->getWarPower() * 0.9);
        if($self instanceof WarUnitGeneral){
            $self->addLevelExp($self->getWarPower() * 0.9 / 50);
        }

        $self->setWarPowerMultiply(0);
        $oppose->setWarPowerMultiply(0);

        return false; //저지는 모든 이벤트를 중지시킨다.
    }
}