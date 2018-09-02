<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_덕가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '덕가';
    static $info = '';
    static $pros = '치안↑ 인구↑ 민심↑';
    static $cons = '쌀수입↓ 수성↓';

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'rice'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}