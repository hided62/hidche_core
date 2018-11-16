<?php
namespace sammo\ActionNationType;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;

class che_치료_도소연명 extends \sammo\BaseItem{

    static $id = 9;
    static $name = '도소연명(치료)';
    static $info = '[군사] 턴 실행 전 부상 회복.';
    static $cost = 200;
    static $consumable = false;

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