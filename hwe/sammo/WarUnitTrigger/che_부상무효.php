<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;

class che_부상무효 extends BaseWarUnitTrigger{
    static protected $priority = ObjectTrigger::PRIORITY_BEGIN + 200;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        $oppose->activateSkill('저격불가');
        $self->activateSkill('부상무효');

        return true;
    }
}