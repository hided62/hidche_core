<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_병가 extends \sammo\BaseNation{

    protected $name = '병가';
    protected $info = '';
    static $pros = '기술↑ 수성↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '기술'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        else if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }

        return $value;
    }

    public function onCalcNationalIncome(string $type, $amount):int{
        if($type == 'pop' && $amount > 0){
            return $amount * 0.8;
        }

        return $amount;
    }
}