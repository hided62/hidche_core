<?php
namespace sammo\ActionItem;
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

class event_전투특기_의술 extends \sammo\BaseItem{

    protected $id = 73;
    protected $rawName = '비급';
    protected $name = '비급(의술)';
    protected $info = '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

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
