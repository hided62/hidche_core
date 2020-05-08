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

    public function action(?array $env=null, $arg=null):?array{

        /** @var \sammo\General $general */
        $general = $this->object;
        $logger = $general->getLogger();
        
        if($general->getVar('injury') > 0){
            $general->updateVar('injury', 0);
            $general->activateSkill('pre.부상경감', 'pre.치료');
            $logger->pushGeneralActionLog('<C>의술</>을 펼쳐 스스로 치료합니다!', ActionLogger::PLAIN);
        }

        $db = DB::db();

        $patients = $db->queryAllLists(
            'SELECT no,name,nation FROM general WHERE city=%i AND injury > 10 AND no != %i', 
            $general->getVar('city'), 
            $general->getID()
        );

        if(!$patients){
            return $env;
        }

        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $cureList = [];

        foreach($patients as [$patientID, $patientName, $patientNationID]){
            if (!Util::randBool(0.5)) {
                continue;
            }

            $cureList[] = $patientID;
            $patientLogger = new ActionLogger($patientID, $patientNationID, $logger->getYear(), $logger->getMonth());
            $patientLogger->pushGeneralActionLog("<Y>{$generalName}</>{$josaYi} <C>의술</>로써 치료해줍니다!", ActionLogger::PLAIN);
            $patientLogger->flush();
        }

        if(!$cureList){
            return $env;
        }

        
        if(count($cureList) == 1){
            $josaUl = JosaUtil::pick($patientName, "을");
            $logger->pushGeneralActionLog("<C>의술</>을 펼쳐 도시의 장수 <Y>{$patientName}</>{$josaUl} 치료합니다!", ActionLogger::PLAIN);
        }
        else{
            $otherCount = count($cureList) - 1;
            $logger->pushGeneralActionLog("<C>의술</>을 펼쳐 도시의 장수들 <Y>{$patientName}</> 외 <C>{$otherCount}</>명을 치료합니다!", ActionLogger::PLAIN);
        }

        $db->update('general', [
            'injury'=>0
        ], 'no IN %li', $cureList);

        return $env;
    }
}