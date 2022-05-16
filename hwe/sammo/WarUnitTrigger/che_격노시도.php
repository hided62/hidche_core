<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;
use sammo\Util;

class che_격노시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 300;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$oppose->hasActivatedSkill('필살') && !$oppose->hasActivatedSkill('회피')){
            return true;
        }
        if($self->hasActivatedSkill('격노불가')){
            return true;
        }

        if($oppose->hasActivatedSkill('필살')){
            $self->activateSkill('격노');
            $oppose->deactivateSkill('회피');
            if($self->isAttacker() && $self->rng->nextBool(1/2)){
                $self->activateSkill('진노');
            }
        }
        else if($self->rng->nextBool(1/4)){
            $self->activateSkill('격노');
            $oppose->deactivateSkill('회피');
            if($self->isAttacker() && $self->rng->nextBool(1/2)){
                $self->activateSkill('진노');
            }
        }
        return true;
    }
}