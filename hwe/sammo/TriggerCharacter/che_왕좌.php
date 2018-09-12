<?php
namespace sammo\TriggerCharacter;
use \sammo\iActionTrigger;
use \sammo\General;

class che_대의 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 0;
    static $name = '왕좌';
    static $info = '명성 +10%, 사기 -5';

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        if($statName == 'experience'){
            return $value * 1.1;
        }
        if($statName == 'bonusAtmos'){
            return $value - 5;
        }
        return $value;
    }
}