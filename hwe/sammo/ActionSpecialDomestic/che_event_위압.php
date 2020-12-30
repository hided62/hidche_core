<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_위압시도;
use \sammo\WarUnitTrigger\che_위압발동;

class che_event_위압 extends \sammo\BaseSpecial{

    protected $id = 63;
    protected $name = '위압';
    protected $info = '[전투] 첫 페이즈 위압 발동(적 공격, 회피 불가, 사기 5 감소)';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_위압시도($unit),
            new che_위압발동($unit)
        );
    }
}