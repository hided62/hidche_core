<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;
use sammo\Util;

class che_약탈시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE + 400;

    protected $ratio;
    protected $theftRatio;

    public function __construct(WarUnit $unit, int $raiseType, float $ratio, float $theftRatio){
        $this->object = $unit;
        $this->raiseType = $raiseType;
        $this->ratio = $ratio;
        $this->theftRatio = $theftRatio;
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if($self->getPhase() !== 0 && $oppose->getPhase() !== 0){
            return true;
        }
        if(!($oppose instanceof WarUnitGeneral)){
            return true;
        }
        if($self->hasActivatedSkill('약탈')){
            return true;
        }
        if($self->hasActivatedSkill('약탈불가')){
            return true;
        }
        if(!$self->rng->nextBool($this->ratio)){
            return true;
        }

        $self->activateSkill('약탈');
        $selfEnv['theftRatio'] = $this->theftRatio;

        return true;
    }
}