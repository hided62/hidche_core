<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_도소연명 extends \sammo\BaseItem{

    protected $rawName = '도소연명';
    protected $name = '도소연명(치료)';
    protected $info = '[군사] 턴 실행 전 부상 회복.';
    protected $cost = 200;
    protected $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_아이템치료($general)
        );
    }
}