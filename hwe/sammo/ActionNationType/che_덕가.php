<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_덕가 extends \sammo\BaseNation{

    protected $name = '덕가';
    protected $info = '';
    static $pros = '치안↑ 인구↑ 민심↑';
    static $cons = '쌀수입↓ 수성↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '치안'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }
        else if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, $amount){
        if($type == 'rice'){
            return $amount * 0.9;
        }
        if($type == 'pop' && $amount > 0){
            return $amount * 1.2;
        }
        
        return $amount;
    }
}