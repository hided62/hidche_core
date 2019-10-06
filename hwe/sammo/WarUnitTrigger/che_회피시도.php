<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;

class che_회피시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE + 200;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!($self instanceof WarUnitGeneral)){
            return true;
        }
        if($self->hasActivatedSkill('특수')){
            return true;
        }
        if($self->hasActivatedSkill('회피불가')){
            return true;
        }

        if(!Util::randBool($self->getComputedAvoidRatio())){
            return true;
        }

        $self->activateSkill('특수', '회피시도', '회피');

        
        return true;
    }
}