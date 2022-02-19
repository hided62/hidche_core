<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_환약 extends \sammo\BaseItem{

    protected $rawName = '환약';
    protected $name = '환약(치료)';
    protected $info = '[군사] 턴 실행 전 부상 회복. 1회용';
    protected $cost = 100;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 0;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_아이템치료($general, $general->getAuxVar('use_treatment')??10)
        );
    }

    function isConsumableNow(string $actionType, string $command):bool{
        if($actionType == 'GeneralTrigger' && $command == 'che_아이템치료'){
            return true;
        }
        return false;
    }
}