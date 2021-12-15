<?php

namespace sammo\WarUnitTrigger;

use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;
use sammo\ActionLogger;
use sammo\GameConst;

class che_저격발동 extends BaseWarUnitTrigger
{
    protected $priority = ObjectTrigger::PRIORITY_POST + 100;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv): bool
    {
        if (!$self->hasActivatedSkill('저격')) {
            return true;
        }

        if ($selfEnv['저격발동'] ?? false) {
            return true;
        }
        $selfEnv['저격발동'] = true;

        $general = $self->getGeneral();

        if ($oppose instanceof WarUnitGeneral) {
            $self->getLogger()->pushGeneralActionLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $self->getLogger()->pushGeneralBattleDetailLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $oppose->getLogger()->pushGeneralActionLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);
            $oppose->getLogger()->pushGeneralBattleDetailLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);
        }
        else{
            $self->getLogger()->pushGeneralActionLog("성벽 수비대장을 <C>저격</>했다!", ActionLogger::PLAIN);
            $self->getLogger()->pushGeneralBattleDetailLog("성벽 수비대장을 <C>저격</>했다!", ActionLogger::PLAIN);
        }

        $general->increaseVarWithLimit('atmos', $selfEnv['addAtmos'], 0, GameConst::$maxAtmosByWar);

        if (!$oppose->hasActivatedSkill('부상무효') && $oppose instanceof WarUnitGeneral) {
            $oppose->getGeneral()->increaseVarWithLimit('injury', Util::randRangeInt($selfEnv['woundMin'], $selfEnv['woundMax']), null, 80);
        }

        $this->processConsumableItem();

        return true;
    }
}
