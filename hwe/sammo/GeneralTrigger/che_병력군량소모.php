<?php
namespace sammo\GeneralTrigger;
use sammo\BaseGeneralTrigger;
use sammo\General;
use sammo\ActionLogger;
use sammo\DB;
use sammo\Util;
use sammo\JosaUtil;

class che_병력군량소모 extends BaseGeneralTrigger{
    protected $priority = 50000;

    public function action(\sammo\RandUtil $rng, ?array $env=null, $arg=null):?array{

        /** @var \sammo\General $general */
        $general = $this->object;

        if($general->getVar('crew') >= 100){
            $currentRice = $general->getVar('rice');
            $consumeRice = Util::toInt($general->getVar('crew') / 100);
            if($consumeRice <= $currentRice){
                $general->increaseVar('rice', -$consumeRice);
            }
            else{
                $db = DB::db();
                $crewUp = $general->onCalcDomestic('징집인구', 'score', $general->getVar('crew'));
                $db->update('city', [
                    'pop'=>$db->sqleval('pop + %i', $crewUp)
                ], 'city=%i', $general->getCityID());

                $general->setVar('crew', 0);
                $general->setVar('rice', 0);
                $general->getLogger()->pushGeneralActionLog(
                    '군량이 모자라 병사들이 <R>소집해제</>되었습니다!', ActionLogger::PLAIN
                );

                $general->activateSkill('pre.소집해제');
            }
            $general->activateSkill('pre.병력군량소모');
        }

        return $env;
    }
}