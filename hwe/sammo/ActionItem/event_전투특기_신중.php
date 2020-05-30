<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;

class event_전투특기_신중 extends \sammo\BaseItem{

    protected $id = 44;
    protected $rawName = '비급';
    protected $name = '비급(신중)';
    protected $info = '[전투] 계략 성공 확률 100%';
    protected $cost = 100;
    protected $buyable = true;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warMagicSuccessProb'){
            return $value + 1;
        }
        return $value;
    }
}