<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_법가 extends \sammo\BaseNation{

    protected $name = '법가';
    protected $info = '';
    static $pros = '금수입↑ 치안↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '치안'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        } 
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return Util::toInt($amount * 1.1);
        }
        if($type == 'pop'){
            return Util::toInt($amount * 0.8);
        }
        
        return $amount;
    }
}