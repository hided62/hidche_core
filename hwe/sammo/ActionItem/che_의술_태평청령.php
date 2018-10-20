<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_의술_태평청령 extends \sammo\BaseItem{

    public $id = 24;
    public $name = '태평청령(의술)';
    public $info = '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)';
    public $cost = 200;
    public $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller([
            new GeneralTrigger\che_도시치료($general)
        ]);
    }
}