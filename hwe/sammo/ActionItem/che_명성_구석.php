<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_명성_구석 extends \sammo\BaseItem{

    protected $rawName = '구석';
    protected $name = '구석(명성)';
    protected $info = '명성 +15%';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'experience'){
            return $value * 1.15;
        }
        return $value;
    }
}