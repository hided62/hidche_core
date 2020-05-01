<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_태평도 extends \sammo\BaseNation{

    protected $name = '태평도';
    protected $info = '';
    static $pros = '인구↑ 민심↑';
    static $cons = '기술↓ 수성↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '기술'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'pop'){
            return $amount * 1.2;
        }
        
        return $amount;
    }
}