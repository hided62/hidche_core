<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;
use sammo\WarUnitTrigger\che_전투치료발동;
use sammo\WarUnitTrigger\che_전투치료시도;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitTriggerCaller;

class che_의술_청낭서 extends \sammo\BaseItem{

    protected $rawName = '청낭서';
    protected $name = '청낭서(의술)';
    protected $info = '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 40% 확률로 치료 발동(아군 피해 30% 감소, 부상 회복)';
    protected $cost = 200;
    protected $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_도시치료($general)
        );
    }

    public function getBattlePhaseSkillTriggerList(\sammo\WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new che_전투치료시도($unit, BaseWarUnitTrigger::TYPE_ITEM+BaseWarUnitTrigger::TYPE_DEDUP_TYPE_BASE*302),
            new che_전투치료발동($unit, BaseWarUnitTrigger::TYPE_ITEM)

        );
    }
}