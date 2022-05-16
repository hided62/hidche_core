<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_저지시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE; //최 우선 순위

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if($self->isAttacker()){
            return true;
        }
        if($self->hasActivatedSkill('특수')){
            return true;
        }
        if($self->hasActivatedSkill('저지불가')){
            return true;
        }

        $ratio = $self->getComputedAtmos() + $self->getComputedTrain();
        if($self->rng->nextBool($ratio / 400)){
            $self->activateSkill('특수', '저지');
        }

        return true;
    }
}