<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

class BaseStatItem extends BaseItem{

    protected static $statType = 'leader';
    protected static $statValue = 0;
    protected static $cost = 1000;
    protected static $rawName = '';
    protected static $consumable = false;
    protected const STAT_NICK = [
        'leader'=>'통솔',
        'power'=>'무력',
        'intel'=>'지력'
    ];

    function getID(){
        return $this->statValue;
    }
    function getName(){
        return sprintf('%s(+%d)',$this->rawName, $this->statValue);
    }
    function getInfo(){
        return sprintf('%s +%d', static::STAT_NICK[$this->statType], $this->statValue);
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === $this->statType){
            return $value + $this->statValue;
        }
        return $value;
    }
}