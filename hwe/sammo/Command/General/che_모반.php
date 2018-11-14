<?php
namespace sammo\GeneralCommand;

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

class che_모반 extends Command\GeneralCommand{
    static protected $actionName = '모반';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setNation();

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'nation'], 1);
        $this->setDestGeneral($destGeneral);
        
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

        $destGeneral = $this->destGeneralObj;

        $generalName = $general->getName();
        $destGeneralName = $destGeneral->getName();

        $nationName = $this->nation['name'];
        
        $logger = $general->getLogger();
        $destLogger = $destGeneral->getLogger();

        $destGeneral->setVar('level', 12);
        $general->setVar('level', 1);
        $general->multiplyVar('experience', 0.7);

        $db->update('city', [
            'gen1'=>0
        ], 'gen1=%i', $destGeneral->getID());
        $db->update('city', [
            'gen2'=>0
        ], 'gen2=%i', $destGeneral->getID());
        $db->update('city', [
            'gen3'=>0
        ], 'gen3=%i', $destGeneral->getID());

        $destGeneral->increaseVarWithLimit($resKey, $amount);
        $general->increaseVarWithLimit($resKey, -$amount, 0);

        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalHistoryLog("<Y><b>【선양】</b></><Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>의 군주 자리를 <Y>{$destGeneralName}</>에게 선양했습니다.");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <Y>{$destGeneralName}</>에게 선양");

        $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 군주의 자리를 물려줍니다. <1>$date</>");
        $destLogger->pushGeneralActionLog("<Y>{$generalName}</>에게서 군주의 자리를 물려받습니다.");

        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>의 군주자리를 <Y>{$destGeneralName}</>에게 선양");
        $destLogger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>의 군주자리를 물려 받음");

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }

    
}