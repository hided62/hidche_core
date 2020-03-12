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

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    GetImageURL
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Event\Action;

class che_필사즉생 extends Command\NationCommand{
    static protected $actionName = '필사즉생';
    static public $reqArg = false;

    protected function argTest():bool{
        $this->arg = [];

        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['strategic_cmd_limit']);
        
        $this->runnableConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::AllowDiplomacyStatus($this->generalObj->getNationID(), [
                0
            ], '전쟁중이 아닙니다.'),
            ConstraintHelper::ReqNationValue('strategic_cmd_limit', '전략기한', '==', 0, '전략기한이 남았습니다.')
        ];
    }
    
    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 2;
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
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);
        
        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("필사즉생 발동! <1>$date</>");

        $exp = 5 * ($this->getPreReqTurn() + 1);
        $ded = 5 * ($this->getPreReqTurn() + 1);

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $josaYi = JosaUtil::pick($generalName, '이');

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <M>필사즉생</>을 발동하였습니다.";

        foreach($db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID) as $targetGeneralID){
            $targetGeneral = General::createGeneralObjFromDB($targetGeneralID, ['train', 'atmos'], 1);
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

        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount*8)*10);    

        $nextTerm = $general->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        $db->update('nation', ['strategic_cmd_limit' => $nextTerm], 'nation=%i', $nationID);

        $general->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);

        return true;
    }
}