<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_무쌍 extends \sammo\BaseItem{

    protected $id = 61;
    protected $rawName = '비급';
    protected $name = '비급(무쌍)';
    protected $info = '[전투] 대미지 +10%, 공격 시 필살 확률 +10%p';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warCriticalRatio' && $aux['isAttacker']??false){
            return $value += 0.1;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1.1, 1];
    }
}