<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\SpecialityHelper;
use \sammo\WarUnit;
use sammo\WarUnitTriggerCaller;
use sammo\WarUnitTrigger\che_격노시도;
use sammo\WarUnitTrigger\che_격노발동;

class event_빼빼로 extends \sammo\BaseItem{

    protected $rawName = '빼빼로';
    protected $name = '빼빼로';
    protected $info = '1의 상징입니다.<br>통솔 +1, 무력 +1, 지력 +1';
    protected $cost = 1500;
    protected $buyable = true;
    protected $consumable = false;
    protected $reqSecu = 12000;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if(in_array($statName, ['leadership', 'strength', 'intel'] )){
            return $value + 1;
        }
        return $value;
    }

}