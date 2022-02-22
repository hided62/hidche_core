<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;

class che_치료_환약 extends \sammo\BaseItem{

    protected $rawName = '환약';
    protected $name = '환약(치료)';
    protected $info = '[군사] 턴 실행 전 부상 회복. 3회용';
    protected $cost = 200;
    protected $consumable = true;
    protected $buyable = true;
    protected $reqSecu = 0;

    const REMAIN_KEY = 'remain환약';

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_아이템치료($general, $general->getAuxVar('use_treatment')??10)
        );
    }

    function onArbitraryAction(General $general, string $actionType, ?string $phase = null, $aux = null): ?array
    {
        if($actionType != '장비매매'){
            return $aux;
        }
        if($phase != '구매'){
            return $aux;
        }

        $general->setAuxVar(static::REMAIN_KEY, 3);
        return $aux;
    }

    function tryConsumeNow(General $general, string $actionType, string $command):bool{
        if($actionType != 'GeneralTrigger'){
            return false;
        }
        if($command != 'che_아이템치료'){
            return false;
        }
        $remainCnt = $general->getAuxVar(static::REMAIN_KEY)??1;
        if($remainCnt > 1){
            $general->setAuxVar(static::REMAIN_KEY, $remainCnt - 1);
            return false;
        }

        $general->setAuxVar(static::REMAIN_KEY, null);
        return true;
    }
}