<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use \sammo\WarUnitTrigger\che_저격시도;
use \sammo\WarUnitTrigger\che_저격발동;

class che_저격 extends \sammo\BaseSpecial{

    protected $id = 70;
    protected $name = '저격';
    protected $info = '[전투] 전투 개시 시 1/3 확률로 저격 발동';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_저격시도($unit, che_저격시도::TYPE_NONE, 1/3, 20, 60),
            new che_저격발동($unit)
        );
    }
}