<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_환약 extends \sammo\BaseItem{

    protected static $id = 1;
    protected static $name = '환약(치료)';
    protected static $info = '[군사] 턴 실행 전 부상 회복. 1회용';
    protected static $cost = 100;
    protected static $consumable = true;

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