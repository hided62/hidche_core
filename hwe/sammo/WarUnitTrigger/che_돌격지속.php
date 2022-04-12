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

class che_돌격지속 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 900;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        /** @var WarUnitGeneral $self */
        if($oppose instanceof WarUnitCity){
            return true;
        }
        if(!$self->isAttacker()){
            return true;
        }
        $attackCoef = $self->getCrewType()->getAttackCoef($oppose->getCrewType());
        if($attackCoef < 1){
            if($oppose->hasActivatedSkill('선제') && $self->getPhase() >= $self->getMaxPhase() - 2){
                $self->addBonusPhase(-1);
            }
            return true;
        }
        if($self->getPhase() < $self->getMaxPhase() - 1){
            return true;
        }
        $self->addBonusPhase(1);
        return true;
    }
}