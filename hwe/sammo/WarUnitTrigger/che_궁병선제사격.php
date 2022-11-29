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

class che_궁병선제사격 extends BaseWarUnitTrigger
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
        if ($oppose->hasActivatedSkill('선제')){
            //맞 선제는 한쪽에서 같이 처리
            return true;
        }

        $self->activateSkill('특수', '선제');
        $self->addPhase(-1);
        $oppose->addPhase(-1);
        if ($oppose->getCrewType()->armType == GameUnitConst::T_ARCHER) {
            $oppose->activateSkill('특수', '선제');

            $self->multiplyWarPowerMultiply(0.5);
            $oppose->multiplyWarPowerMultiply(0.5);
            $oppose->getLogger()->pushGeneralBattleDetailLog('서로 <C>선제 사격</>을 주고 받았다!</>', ActionLogger::PLAIN);
            $self->getLogger()->pushGeneralBattleDetailLog('서로 <C>선제 사격</>을 주고 받았다!</>', ActionLogger::PLAIN);
            return true;
        }

        $oppose->multiplyWarPowerMultiply(0);
        $self->multiplyWarPowerMultiply(2/3);
        $self->activateSkill('회피불가', '필살불가', '계략불가');
        $oppose->activateSkill('회피불가', '필살불가', '격노불가', '계략불가');

        $oppose->getLogger()->pushGeneralBattleDetailLog('상대에게 <R>선제 사격</>을 받았다!</>', ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog('상대에게 <C>선제 사격</>을 했다!</>', ActionLogger::PLAIN);

        return true;
    }
}
