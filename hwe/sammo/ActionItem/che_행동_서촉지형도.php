<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_행동_서촉지형도 extends \sammo\BaseItem{

    protected $id = 22;
    protected $rawName = '서촉지형도';
    protected $name = '서촉지형도(행동)';
    protected $info = '[전투] 공격 시 페이즈 + 2';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName === 'initWarPhase'){
            return $value + 2;
        }
        return $value;
    }
}