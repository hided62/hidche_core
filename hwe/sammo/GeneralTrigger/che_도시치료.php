<?php
namespace sammo\GeneralTrigger;
use sammo\BaseGeneralTrigger;
use sammo\General;
use sammo\ActionLogger;
use sammo\DB;
use sammo\Util;
use sammo\JosaUtil;

class che_도시치료 extends BaseGeneralTrigger{
    protected $priority = 10010;

    public function action(\sammo\RandUtil $rng, ?array $env=null, $arg=null):?array{

        /** @var \sammo\General $general */
        $general = $this->object;
        $logger = $general->getLogger();

        if($general->getVar('injury') > 0){
            $general->updateVar('injury', 0);
            $general->activateSkill('pre.부상경감', 'pre.치료');
            $logger->pushGeneralActionLog('<C>의술</>을 펼쳐 스스로 치료합니다!', ActionLogger::PLAIN);
        }

        $db = DB::db();

        /** @var array{int,string,string}[] $patients */
        $patients = $db->queryAllLists(
            'SELECT no,name,nation FROM general WHERE city=%i AND injury > 10 AND no != %i',
            $general->getCityID(),
            $general->getID()
        );

        if(!$patients){
            return $env;
        }

        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $cureList = [];

        /** @var string|null */
        $curedPatientName = null;
        foreach($patients as [$patientID, $patientName, $patientNationID]){
            if (!$rng->nextBool(0.5)) {
                continue;
            }

            $cureList[] = $patientID;
            $curedPatientName = $patientName;
            $patientLogger = new ActionLogger($patientID, $patientNationID, $logger->getYear(), $logger->getMonth());
            $patientLogger->pushGeneralActionLog("<Y>{$generalName}</>{$josaYi} <C>의술</>로써 치료해줍니다!", ActionLogger::PLAIN);
            $patientLogger->flush();
        }

        if(!$cureList){
            return $env;
        }

        if($curedPatientName === null){
            throw new \sammo\MustNotBeReachedException();
        }


        if(count($cureList) == 1){
            $josaUl = JosaUtil::pick($curedPatientName, "을");
            $logger->pushGeneralActionLog("<C>의술</>을 펼쳐 도시의 장수 <Y>{$curedPatientName}</>{$josaUl} 치료합니다!", ActionLogger::PLAIN);
        }
        else{
            $otherCount = count($cureList) - 1;
            $logger->pushGeneralActionLog("<C>의술</>을 펼쳐 도시의 장수들 <Y>{$curedPatientName}</> 외 <C>{$otherCount}</>명을 치료합니다!", ActionLogger::PLAIN);
        }

        $db->update('general', [
            'injury'=>0
        ], 'no IN %li', $cureList);

        return $env;
    }
}