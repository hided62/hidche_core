<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_음양가 extends \sammo\BaseNation{

    protected $name = '음양가';
    protected $info = '';
    static $pros = '내정↑ 인구↑';
    static $cons = '기술↓ 전략↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '농업' || $turnType == '상업'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '기술'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }

        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'pop'){
            return Util::toInt($amount * 1.2);
        }
        
        return $amount;
    }

    public function onCalcStrategic(string $turnType, string $varType, $value){
        if($varType == 'delay'){
            return $value * 2;
        }
        return $value;
    }
}