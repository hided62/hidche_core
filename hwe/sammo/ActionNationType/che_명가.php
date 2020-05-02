<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_명가 extends \sammo\BaseNation{

    protected $name = '명가';
    protected $info = '';
    static $pros = '기술↑ 인구↑';
    static $cons = '쌀수입↓ 수성↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '기술'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return Util::toInt($amount * 0.9);
        }
        if($type == 'pop'){
            return Util::toInt($amount * 1.2);
        }
        
        return $amount;
    }
}