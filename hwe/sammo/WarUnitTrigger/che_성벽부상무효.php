<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;

class che_성벽부상무효 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN + 150;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if(!$oppose instanceof WarUnitCity){
            return true;
        }

        $self->activateSkill('부상무효');

        return true;
    }
}