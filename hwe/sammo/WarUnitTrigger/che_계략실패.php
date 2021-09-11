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

class che_계략실패 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 200;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$self->hasActivatedSkill('계략실패')){
            return true;
        }

        if($selfEnv['계략실패']??false){
            return true;
        }
        $selfEnv['계략실패'] = true;

        $general = $self->getGeneral();

        [$magic, $damage] = $selfEnv['magic'];

        $damage = $general->onCalcStat($general, 'warMagicFailDamage', $damage, $magic);
        $damage = $oppose->getGeneral()->onCalcOpposeStat($general, 'warMagicFailDamage', $damage, $magic);
        $josaUl = \sammo\JosaUtil::pick($magic, '을');

        $general->getLogger()->pushGeneralBattleDetailLog("<D>{$magic}</>{$josaUl} <R>실패</>했다!", ActionLogger::PLAIN);
        $oppose->getLogger()->pushGeneralBattleDetailLog("<D>{$magic}</>{$josaUl} 간파했다!", ActionLogger::PLAIN);

        $self->multiplyWarPowerMultiply(1/$damage);
        $oppose->multiplyWarPowerMultiply($damage);

        return true;
    }
}