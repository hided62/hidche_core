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

class che_격노발동 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 600;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('격노')){
            return true;
        }

        $targetAct = $oppose->hasActivatedSkill('필살')?'필살 공격':'회피 시도';
        $is진노 = $self->hasActivatedSkill('진노');
        $reaction = $is진노?'진노':'격노';

        $self->getLogger()->pushGeneralBattleDetailLog("상대의 {$targetAct}에 <C>{$reaction}</>했다!</>", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("{$targetAct}에 상대가 <R>{$reaction}</>했다!</>", ActionLogger::PLAIN);
         
        if($is진노){
            $self->bonusPhase += 1;
        }
        $self->multiplyWarPowerMultiply($self->criticalDamage());

        return true;
    }
}