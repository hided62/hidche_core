<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_전투치료시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE + 350;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if($self->hasActivatedSkill('치료')){
            return true;
        }
        if($self->hasActivatedSkill('치료불가')){
            return true;
        }
        if(!$self->rng->nextBool(0.4)){
            return true;
        }

        $self->activateSkill('치료');


        return true;
    }
}