<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_불가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '불가';
    static $info = '';
    static $pros = '민심↑ 수성↑';
    static $cons = '금수입↓';

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 0.9;
        }
        
        return $amount;
    }
}