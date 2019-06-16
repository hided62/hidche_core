<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_모반시도 extends Command\GeneralCommand{
    static protected $actionName = '모반시도';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setNation();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::NotLord(),
            ConstraintHelper::AllowRebellion(),
        ];
    }

    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $nationID = $general->getNationID();

        $lordID = $db->queryFirstField('SELECT no FROM general WHERE nation = %i AND level = 12', $nationID);

        $lordGeneral = General::createGeneralObjFromDB($lordID);

        $generalName = $general->getName();
        $lordName = $lordGeneral->getName();

        $nationName = $this->nation['name'];
        
        $logger = $general->getLogger();
        $lordLogger = $this->destGeneralObj->getLogger();

        $general->setVar('level', 12);
        $lordGeneral->setVar('level', 1);
        $lordGeneral->multiplyVar('experience', 0.7);

        $db->update('city', [
            'gen1'=>0
        ], 'gen1=%i', $general->getID());
        $db->update('city', [
            'gen2'=>0
        ], 'gen2=%i', $general->getID());
        $db->update('city', [
            'gen3'=>0
        ], 'gen3=%i', $general->getID());

        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalHistoryLog("<Y><b>【모반】</b></><Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>의 군주 자리를 찬탈했습니다.");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <Y>{$lordName}</>에게서 군주자리를 찬탈");

        $logger->pushGeneralActionLog("모반에 성공했습니다. <1>$date</>");
        $lordLogger->pushGeneralActionLog("<Y>{$generalName}</>에게 군주의 자리를 뺏겼습니다.");

        $logger->pushGeneralHistoryLog("모반으로 <D><b>{$nationName}</b></>의 군주자리를 찬탈");
        $lordLogger->pushGeneralHistoryLog("<D><b>{$generalName}</b></>의 모반으로 인해 <D><b>{$nationName}</b></>의 군주자리를 박탈당함");

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);
        $lordGeneral->applyDB($db);

        return true;
    }

    
}