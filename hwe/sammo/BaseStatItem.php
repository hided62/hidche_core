<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

class BaseStatItem extends BaseItem{

    protected $statNick = '통솔';
    protected $statType = 'leadership';
    protected $statValue = 1;
    protected $cost = 1000;
    protected $rawName = '노기';
    protected $consumable = false;
    protected $buyable = true;
    
    protected const ITEM_TYPE = [
        '명마'=>['통솔', 'leadership'],
        '무기'=>['무력', 'strength'],
        '서적'=>['지력', 'intel']
    ];

    public function __construct(){
        $nameTokens = explode('_', static::class);
        $this->statValue = (int)$nameTokens[-1];
        assert(is_numeric($this->statValue));
        $this->rawName = $nameTokens[-2];
        [$this->statNick, $this->statType] = static::ITEM_TYPE[$nameTokens[-3]];

        $this->id = $this->statValue;
        $this->name = sprintf('%s(+%d)',$this->rawName, $this->statValue);
        $this->info = sprintf('%s +%d', $this->statNick, $this->statValue);
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === $this->statType){
            return $value + $this->statValue;
        }
        return $value;
    }
}