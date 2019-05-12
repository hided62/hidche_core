<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;

class che_저격시도 extends BaseWarUnitTrigger{
    static protected $priority = ObjectTrigger::PRIORITY_PRE + 100;

    protected $woundMin;
    protected $woundMax;
    protected $ratio;

    public function __construct(WarUnit $unit, int $raiseType, float $ratio, float $woundMin, float $woundMax){
        $this->object = $unit;
        $this->raiseType = $raiseType;
        $this->ratio = $ratio;
        $this->woundMin = $woundMin;
        $this->woundMax = $woundMax;
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if(!$oppose instanceof WarUnitGeneral){
            return true;
        }
        if($self->hasActivatedSkill('저격')){
            return true;
        }
        if($self->hasActivatedSkill('저격불가')){
            return true;
        }
        if(!Util::randBool($this->ratio)){
            return true;
        }

        $this->activateSkill('저격');
        $selfEnv['woundMin'] = $this->woundMin;
        $selfEnv['woundMax'] = $this->woundMax;

        return true;
    }
}