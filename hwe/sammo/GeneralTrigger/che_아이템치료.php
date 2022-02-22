<?php
namespace sammo\GeneralTrigger;
use sammo\BaseGeneralTrigger;
use sammo\General;
use sammo\ActionLogger;
use sammo\DB;
use sammo\Util;
use sammo\JosaUtil;

class che_아이템치료 extends BaseGeneralTrigger{
    protected $priority = 20010;
    protected $injuryTarget = 10;

    public function __construct(General $general, int $injuryTarget=10){
        $this->object = $general;
        $this->injuryTarget = $injuryTarget;
    }

    public function action(?array $env=null, $arg=null):?array{

        /** @var \sammo\General $general */
        $general = $this->object;

        if($general->getVar('injury') >= $this->injuryTarget){
            if($general->getReservedTurn(0, $env) instanceof \sammo\Command\General\che_요양){
                return $env;
            }

            $general->updateVar('injury', 0);
            $general->activateSkill('pre.부상경감', 'pre.치료');
            $itemObj = $general->getItem();
            $itemName = $itemObj->getName();
            $itemRawName = $itemObj->getRawName();
            $josaUl = JosaUtil::pick($itemRawName, '을');
            $general->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용하여 치료합니다!", ActionLogger::PLAIN);

            if($itemObj->tryConsumeNow($general, 'GeneralTrigger', 'che_아이템치료')){
                $general->deleteItem();
            }
        }

        return $env;
    }
}