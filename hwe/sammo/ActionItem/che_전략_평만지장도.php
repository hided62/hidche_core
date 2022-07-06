<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use sammo\Util;

class che_전략_평만지장도 extends \sammo\BaseItem{

    protected $rawName = '평만지장도';
    protected $name = '평만지장도(전략)';
    protected $info = '[전략] 국가전략 사용시 재사용 대기 기간 -20%';
    protected $cost = 200;
    protected $consumable = false;

    public function onCalcStrategic(string $turnType, string $varType, $value){
        if($varType == 'delay'){
            return Util::round($value * 0.80);
        }
        return $value;
    }
}