<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_법가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '법가';
    static $info = '';
    static $pros = '금수입↑ 치안↑';
    static $cons = '인구↓ 민심↓';

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($type == 'gold'){
            return $amount * 1.1;
        }
        
        return $amount;
    }
}