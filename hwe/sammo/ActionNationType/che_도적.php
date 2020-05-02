<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\Util;

class che_도적 extends \sammo\BaseNation{

    protected $name = '도적';
    protected $info = '';
    static $pros = '계략↑';
    static $cons = '금수입↓ 치안↓ 민심↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '치안'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == '민심' || $turnType == '인구'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        else if($turnType == '계략'){
            if($varType == 'success') return $value + 0.1;
        }
        
        
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return Util::toInt($amount * 0.9);
        }
        
        return $amount;
    }
}