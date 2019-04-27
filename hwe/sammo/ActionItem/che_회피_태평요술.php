<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_회피_태평요술 extends \sammo\BaseItem{

    protected static $id = 25;
    protected static $name = '둔갑천서(회피)';
    protected static $info = '[전투] 회피 확률 +20%p';
    protected static $cost = 200;
    protected static $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'warAvoidRatio'){
            return $value += 0.2;
        }
        return $value;
    }
}