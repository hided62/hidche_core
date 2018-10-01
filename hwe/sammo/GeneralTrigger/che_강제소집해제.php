<?php
namespace sammo\GeneralTrigger;
use sammo\BaseGeneralTrigger;
use sammo\General;
use sammo\ActionLogger;
use sammo\DB;
use sammo\Util;
use sammo\JosaUtil;

class che_강제소집해제 extends BaseGeneralTrigger{
    static protected $priority = 50000;

    public function action(?array $env=null, $arg=null):?array{

        /** @var \sammo\General $general */
        $general = $this->object;

        if($general->getVar('crew') >= 100){
            $currentRice = $general->getVar('rice');
            $consumeRice = Util::toInt($general->getVar('crew') / 100);
            if($consumeRice <= $currentRice){
                $general->increaseVar('rice', -$consumeRice);
            }
            else{
                $general->setVar('rice', 0);
                $general->getLogger()->pushGeneralActionLog(
                    '군량이 모자라 병사들이 <R>소집해제</>되었습니다!', ActionLogger::PLAIN
                );
            }
            $general->activateSkill('pre.소집해제');
        }

        return $env;
    }
}