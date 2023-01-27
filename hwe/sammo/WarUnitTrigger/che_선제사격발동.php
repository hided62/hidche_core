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

class che_선제사격발동 extends BaseWarUnitTrigger
{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN + 51;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv): bool
    {
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if (!$self->hasActivatedSkill('선제')) {
            return true;
        }
        if ($oppose->hasActivatedSkill('선제') && $oppose->isAttacker()){
            //맞 선제라면 공격자가 처리
            return true;
        }

        $self->addPhase(-1);
        $oppose->addPhase(-1);
        if ($oppose->hasActivatedSkill('선제')) {
            $self->multiplyWarPowerMultiply(2/3);
            $oppose->multiplyWarPowerMultiply(2/3);
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
