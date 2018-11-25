<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_의술_태평청령 extends \sammo\BaseItem{

    static $id = 24;
    static $name = '태평청령(의술)';
    static $info = '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)';
    static $cost = 200;
    static $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller([
            new GeneralTrigger\che_도시치료($general)
        ]);
    }
}