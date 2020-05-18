<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_오두미도 extends \sammo\BaseNation{

    protected $name = '오두미도';
    protected $info = '';
    static $pros = '쌀수입↑ 인구↑';
    static $cons = '기술↓ 수성↓ 내정↓';


    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '기술'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == '농업' || $turnType == '상업'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, $amount){
        if($type == 'rice'){
            return $amount * 1.1;
        }
        if($type == 'pop' && $amount > 0){
            return $amount * 1.2;
        }
        
        return $amount;
    }

}