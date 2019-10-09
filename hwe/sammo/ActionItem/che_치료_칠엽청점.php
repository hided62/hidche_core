<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_칠엽청점 extends \sammo\BaseItem{

    protected $id = 10;
    protected $name = '칠엽청점(치료)';
    protected $info = '[군사] 턴 실행 전 부상 회복.';
    protected $cost = 200;
    protected $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_아이템치료($general)
        );
    }

    function isConsumableNow(string $actionType, string $command):bool{
        if($actionType == 'GeneralTrigger' && $command == 'che_아이템치료'){
            return true;
        }
        return false;
    }
}