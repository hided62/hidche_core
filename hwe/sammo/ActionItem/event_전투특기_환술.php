<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_환술 extends \sammo\BaseItem{

    protected $id = 42;
    protected $rawName = '비급';
    protected $name = '비급(환술)';
    protected $info = '[전투] 계략 성공 확률 +10%p, 계략 성공 시 대미지 +30%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessProb'){
            return $value + 0.1;
        }
        if($statName === 'warMagicSuccessDamage'){
            return $value * 1.3;
        }
        return $value;
    }
}