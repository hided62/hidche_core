<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;

class WarActivateSkills extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN;

    protected $isSelf;
    protected $activeSkills;

    public function __construct(WarUnit $unit, int $raiseType, bool $isSelf, string ...$activeSkills){
        $this->object = $unit;
        $this->raiseType = $raiseType;
        $this->isSelf = $isSelf;
        $this->activeSkills = $activeSkills;
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if($this->isSelf){
            $self->activateSkill(...$this->activeSkills);
        }
        else{
            $oppose->activateSkill(...$this->activeSkills);
        }
        
        return true;
    }
}