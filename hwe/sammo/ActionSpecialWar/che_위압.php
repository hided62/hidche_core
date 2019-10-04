<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_위압시도;
use \sammo\WarUnitTrigger\che_위압발동;

class che_위압 implements iAction{
    use \sammo\DefaultAction;

    protected $id = 63;
    protected $name = '위압';
    protected $info = '[전투] 훈련/사기≥90, 병력≥1,000 일 때 첫 페이즈 위압 발동(적 공격 불가)';

    static $selectWeightType = SpecialityConst::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_STRENGTH
    ];

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        if($unit->getPhase() != 0){
            return null;
        }
        return new WarUnitTriggerCaller(
            new che_위압시도($unit),
            new che_위압발동($unit)
        );
    }
}