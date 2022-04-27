<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use sammo\BaseGeneralTrigger;
use sammo\SpecialityHelper;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;
use \sammo\WarUnit;
use sammo\WarUnitTrigger\che_전투치료발동;
use sammo\WarUnitTrigger\che_전투치료시도;
use sammo\WarUnitTriggerCaller;

class che_event_의술 extends \sammo\BaseSpecial{

    protected $id = 73;
    protected $name = '의술';
    protected $info = '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 40% 확률로 치료 발동(아군 피해 30% 감소, 부상 회복)';

    static $selectWeightType = SpecialityHelper::WEIGHT_PERCENT;
    static $selectWeight = 2;
    static $type = [
        SpecialityHelper::STAT_LEADERSHIP,
        SpecialityHelper::STAT_STRENGTH,
        SpecialityHelper::STAT_INTEL
    ];

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_도시치료($general)
        );
    }

    public function getBattlePhaseSkillTriggerList(\sammo\WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_전투치료시도($unit),
            new che_전투치료발동($unit)
        );
    }
}
