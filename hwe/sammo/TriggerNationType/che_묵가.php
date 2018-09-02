<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_묵가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '묵가';
    static $info = '';
    static $pros = '수성↑';
    static $cons = '기술↓';

    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($turnType == 'def' || $turnType == 'wall'){
            if($varType == 'score') return $value * 1.1;
            if($varType == 'cost') return $value * 0.8;
        }

        else if($turnType == 'tech'){
            if($varType == 'score') return $value * 0.9;
            if($varType == 'cost') return $value * 1.2;
        }
        
        return $value;
    }
}