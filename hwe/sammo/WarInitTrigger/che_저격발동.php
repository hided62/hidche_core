<?php
namespace sammo\WarInitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;

class che_저격발동 extends BaseWarUnitTrigger{
    static protected $priority = 20000;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):void{
        if(!$oppose->hasActivatedSkill('저격')){
            return;
        }

        if($selfEnv['저격발동']){
            return;
        }
        $selfEnv['저격발동'] = true;

        $general = $self->general;

        $oppose->getLogger()->pushGeneralActionLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralActionLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);
        $self->getLogger()->pushGeneralBattleDetailLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);

        $general->increaseVarWithLimit('injury', Util::randRangeInt($opposeEnv['woundMin'], $opposeEnv['woundMax']), null, 80);
    }
}