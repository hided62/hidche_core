<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_오석산 extends \sammo\BaseItem{

    protected static $id = 7;
    protected static $name = '오석산(치료)';
    protected static $info = '[군사] 턴 실행 전 부상 회복.';
    protected static $cost = 200;
    protected static $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller([
            new GeneralTrigger\che_아이템치료($general)
        ]);
    }

    function isValidTurnItem(string $actionType, string $command):bool{
        if($actionType == 'GeneralTrigger' && $command == 'che_아이템치료'){
            return true;
        }
        return false;
    }
}