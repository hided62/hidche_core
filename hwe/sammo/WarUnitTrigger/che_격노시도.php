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

        if($self->isAttacker()){
            if(Util::randBool(1/3)){
                $self->activateSkill('진노', '격노');
            }
            else if(Util::randBool(1/4)){
                $self->activateSkill('격노');
            }
        }
        else{
            if(Util::randBool(1/2)){
                $self->activateSkill('격노');
            }
        }

        return true;
    }
}