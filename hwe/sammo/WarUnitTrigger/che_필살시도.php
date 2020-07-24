<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;
use sammo\Util;

class che_필살시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE + 100;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!($self instanceof WarUnitGeneral)){
            return true;
        }
        if($self->hasActivatedSkill('특수')){
            return true;
        }
        if($self->hasActivatedSkill('필살불가')){
            return true;
        }

        if(!Util::randBool($self->getComputedCriticalRatio())){
            return true;
        }

        $self->activateSkill('필살시도', '필살');

        
        return true;
    }
}