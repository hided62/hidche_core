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

class che_약탈발동 extends BaseWarUnitTrigger
{
    protected $priority = ObjectTrigger::PRIORITY_POST + 350;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv): bool
    {
        if (!$self->hasActivatedSkill('약탈')) {
            return true;
        }

        if ($selfEnv['약탈발동'] ?? false) {
            return true;
        }
        $selfEnv['약탈발동'] = true;

        $general = $self->getGeneral();

        if (!($oppose instanceof WarUnitGeneral)) {
            return true;
        }

        $opposeGeneral = $oppose->getGeneral();

        $theftGold = $opposeGeneral->getVar('gold') * $selfEnv['theftRatio'];
        $theftRice = $opposeGeneral->getVar('rice') * $selfEnv['theftRatio'];

        $opposeGeneral->increaseVarWithLimit('gold', -$theftGold, 0);
        $opposeGeneral->increaseVarWithLimit('rice', -$theftRice, 0);

        $general->increaseVar('gold', $theftGold);
        $general->increaseVar('rice', $theftRice);

        $self->getLogger()->pushGeneralActionLog("상대를 <C>약탈</>했다!", ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog("상대에게서 금 {$theftGold}, 쌀 {$theftRice} 만큼을 <C>약탈</>했다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralActionLog("상대에게 <R>약탈</>당했다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("상대에게 금 {$theftGold}, 쌀 {$theftRice} 만큼을 <R>약탈</>당했다!", ActionLogger::PLAIN);

        $this->processConsumableItem();

        return true;
    }
}
