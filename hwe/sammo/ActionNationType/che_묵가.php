<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;

class che_묵가 implements iAction{
    use \sammo\DefaultAction;

    static $name = '묵가';
    static $info = '';
    static $pros = '수성↑';
    static $cons = '기술↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '수비' || $turnType == '성벽'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == '기술'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }
}