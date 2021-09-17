<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class che_반계시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_BODY + 300;
    protected $prob;

    public function __construct(WarUnit $unit, int $raiseType = 0, float $prob = 0.4){
        $this->object = $unit;
        $this->raiseType = $raiseType;
        $this->prob = $prob;
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if(!$oppose->hasActivatedSkill('계략')){
            return true;
        }

        if(!Util::randBool($this->prob)){
            return true;
        }

        assert(key_exists('magic', $opposeEnv));

        $self->activateSkill('반계');
        $oppose->deactivateSkill('계략');

        return true;
    }
}