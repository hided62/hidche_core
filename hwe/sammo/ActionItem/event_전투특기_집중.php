<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_집중 extends \sammo\BaseItem{

    protected $id = 43;
    protected $rawName = '비급';
    protected $name = '비급(집중)';
    protected $info = '[전투] 계략 성공 시 대미지 +50%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessDamage'){
            return $value * 1.5;
        }
        return $value;
    }
}