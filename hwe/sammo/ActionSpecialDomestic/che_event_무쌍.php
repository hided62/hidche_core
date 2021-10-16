<?php
namespace sammo\ActionSpecialDomestic;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use \sammo\Util;

class che_event_무쌍 extends \sammo\BaseSpecial{

    protected $id = 61;
    protected $name = '무쌍';
    protected $info = '[전투] 대미지 +10%, 피해 -5%, 공격 시 필살 확률 +10%p, <br>승리 수만큼 대미지 0.20%씩 추가 상승(최대40%)<br>승리 수만큼 피해 0.05%씩 감소(최대30%)';

    static $selectWeightType = SpecialityHelper::WEIGHT_NORM;
    static $selectWeight = 1;
    static $type = [
        SpecialityHelper::STAT_STRENGTH
    ];

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio' && ($aux['isAttacker']??false)){
            return $value += 0.1;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        $generalWarSpecial = $unit->getGeneral()->getSpecialWar();
        if($generalWarSpecial !== null && $generalWarSpecial->getName() == '무쌍'){
            return [1, 1];
        }
        $attackMultiplier = 1;
        $defenceMultiplier = 1;
        $killnum = $unit->getGeneral()->getRankVar('killnum');
        $attackMultiplier += log(max(1, $killnum / 5), 2) / 20;
        $defenceMultiplier -= log(max(1, $killnum / 5), 2) / 50;
        return [$attackMultiplier, $defenceMultiplier];
    }
}