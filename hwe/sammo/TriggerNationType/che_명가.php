<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_명가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '명가';
    static $info = '';
    static $pros = '기술↑ 인구↑';
    static $cons = '쌀수입↓ 수성↓';

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}