<?php
namespace sammo\ActionSpecialDomestic;

use sammo\Enums\RankColumn;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use \sammo\Util;

class che_event_무쌍 extends \sammo\BaseSpecial{

    protected $id = 61;
    protected $name = '무쌍';
    protected $info = '[전투] 대미지 +5%, 피해 -2%, 공격 시 필살 확률 +10%p, <br>승리 수의 로그 비례로 대미지 상승(10회 ⇒ +5%, 40회 ⇒ +15%)<br>승리 수의 로그 비례로 피해 감소(10회 ⇒ -2%, 40회 ⇒ -6%)';

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
        $attackMultiplier = 1.05;
        $defenceMultiplier = 0.98;
        $killnum = $unit->getGeneral()->getRankVar(RankColumn::killnum);
        $attackMultiplier += log(max(1, $killnum / 5), 2) / 20;
        $defenceMultiplier -= log(max(1, $killnum / 5), 2) / 50;
        return [$attackMultiplier, $defenceMultiplier];
    }
}