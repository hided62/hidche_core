<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    Message
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Event\Action;

class che_필사즉생 extends Command\NationCommand{
    static protected $actionName = '필사즉생';

    protected function argTest():bool{
        $this->arg = [];

        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['strategic_cmd_limit']);
        
        $this->fullConditionConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::AllowDiplomacyStatus($this->generalObj->getNationID(), [
                0
            ], '전쟁중이 아닙니다.'),
            ConstraintHelper::AvailableStrategicCommand()
        ];
    }
    
    public function getCommandDetailTitle():string{
        $name = $this->getName();
        $reqTurn = $this->getPreReqTurn()+1;
        $postReqTurn = $this->getPostReqTurn();

        return "{$name}/{$reqTurn}턴(재사용 대기 $postReqTurn)";
    }
    
    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 2;
    }

    public function getPostReqTurn():int{
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount*8)*10);    

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        return $nextTerm;
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);
        
        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("필사즉생 발동! <1>$date</>");

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <M>필사즉생</>을 발동하였습니다.";

        $targetGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach(General::createGeneralObjListFromDB($targetGeneralList, ['train', 'atmos'], 1) as $targetGeneral){
            $targetGeneral->getLogger()->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            if($targetGeneral->getVar('train') < 100){
                $targetGeneral->setVar('train', 100);
            }
            if($targetGeneral->getVar('atmos') < 100){
                $targetGeneral->setVar('atmos', 100);
            } 
            
            $targetGeneral->applyDB($db);
        }

        if($general->getVar('train') < 100){
            $general->setVar('train', 100);
        }
        if($general->getVar('atmos') < 100){
            $general->setVar('atmos', 100);
        }
        $logger->pushGeneralHistoryLog('<M>필사즉생</>을 발동');
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <M>필사즉생</>을 발동");

        $db->update('nation', [
            'strategic_cmd_limit' => $this->generalObj->onCalcStrategic($this->getName(), 'globalDelay', 9)
        ], 'nation=%i', $nationID);

        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);

        return true;
    }
}