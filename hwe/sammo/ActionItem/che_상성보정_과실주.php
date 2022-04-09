<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\WarUnit;
use sammo\WarUnitTrigger\전투력보정;
use sammo\WarUnitTriggerCaller;

class che_상성보정_과실주 extends \sammo\BaseItem{

    protected $rawName = '과실주';
    protected $name = '과실주(상성)';
    protected $info = '[전투] 대등/유리한 병종 전투시 공격력 +10%, 피해 -10%';
    protected $cost = 200;
    protected $consumable = false;

    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        $oppose = $unit->getOppose();
        $attackCoef = $unit->getCrewType()->getAttackCoef($oppose->getCrewType());
        if($attackCoef < 1){
            return null;
        }
        return new WarUnitTriggerCaller(
            new 전투력보정($unit, 1.1, 0.9)
        );
    }
}
