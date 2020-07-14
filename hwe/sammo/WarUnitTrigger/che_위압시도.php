<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;

class che_위압시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN + 100;

    protected $woundMin;
    protected $woundMax;
    protected $ratio;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if($self->getPhase() !== 0 && $oppose->getPhase() !== 0){
            return true;
        }
        if($self->hasActivatedSkill('위압불가')){
            return true;
        }

        $self->activateSkill('위압');
        $oppose->activateSkill('회피불가', '필살불가', '계략불가');
        return true;
    }
}