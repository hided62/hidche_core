<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_계략발동 extends BaseWarUnitTrigger{
    static protected $priority = ObjectTrigger::PRIORITY_POST + 150;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('반계')){
            return true;
        }

        $general = $self->getGeneral();

        [$opposeMagic, $damage] = $opposeEnv['magic'][0];
        

        $josaUl = \sammo\JosaUtil::pick($opposeMagic, '을');

        $general->pushGeneralBattleDetailLog("<C>반계</>로 상대의 <D>{$opposeMagic}</>{$josaUl} 되돌렸다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("<D>{$opposeMagic}</>{$josaUl} <R>역으로</> 당했다!", ActionLogger::PLAIN);

        $self->multiplyWarPowerMultiply($damage);

        return true;
    }
}