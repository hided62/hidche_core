<?php
namespace sammo\ActionSpecialWar;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityConst;

class che_의술 implements iAction{
    use \sammo\DefaultAction;

    static $id = 73;
    static $name = '의술';
    static $info = '[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)';

    static $selectWeightType = SpecialityConst::WEIGHT_PERCENT;
    static $selectWeight = 2;
    static $type = [
        SpecialityConst::STAT_LEADERSHIP,
        SpecialityConst::STAT_POWER,
        SpecialityConst::STAT_INTEL
    ];

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return null;
    }
}