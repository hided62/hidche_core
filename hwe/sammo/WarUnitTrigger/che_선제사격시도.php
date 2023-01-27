<?php

namespace sammo\WarUnitTrigger;

use sammo\ActionLogger;
use sammo\BaseWarUnitTrigger;
use sammo\GameUnitConst;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_선제사격시도 extends BaseWarUnitTrigger
{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN + 50;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv): bool
    {
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if ($self->getPhase() !== 0 && $oppose->getPhase() !== 0) {
            return true;
        }
        if ($self->hasActivatedSkill('선제')) {
            return true;
        }
        if ($self->hasActivatedSkillOnLog('선제')) {
            return true;
        }

        $self->activateSkill('특수', '선제');
        return true;

    }
}
