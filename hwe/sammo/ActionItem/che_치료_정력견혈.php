<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_정력견혈 extends \sammo\BaseItem{

    protected $rawName = '정력견혈';
    protected $name = '정력견혈(치료)';
    protected $info = '[군사] 턴 실행 전 부상 회복.';
    protected $cost = 200;
    protected $consumable = false;

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_아이템치료($general)
        );
    }
}