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

class che_반계발동 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 250;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('반계')){
            return true;
        }

        $general = $self->getGeneral();

        [$opposeMagic, $damage] = $opposeEnv['magic'];


        $josaUl = \sammo\JosaUtil::pick($opposeMagic, '을');

        $general->getLogger()->pushGeneralBattleDetailLog("<C>반계</>로 상대의 <D>{$opposeMagic}</>{$josaUl} 되돌렸다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("<D>{$opposeMagic}</>{$josaUl} <R>역으로</> 당했다!", ActionLogger::PLAIN);

        $self->multiplyWarPowerMultiply($damage);

        return true;
    }
}