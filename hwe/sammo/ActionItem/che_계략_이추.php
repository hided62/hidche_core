<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;

class che_계략_이추 extends \sammo\BaseItem{

    protected $rawName = '이추';
    protected $name = '이추(계략)';
    protected $info = '[계략] 화계·탈취·파괴·선동 : 성공률 +20%p';
    protected $cost = 1000;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 1000;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 0.2;
        }

        return $value;
    }

    function tryConsumeNow(General $general, string $actionType, string $command):bool{
        if($actionType == 'GeneralCommand' && $command == '계략'){
            return true;
        }
        return false;
    }
}