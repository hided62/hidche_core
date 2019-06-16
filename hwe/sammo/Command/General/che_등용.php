<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    ScoutMessage
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_등용 extends Command\GeneralCommand{
    static protected $actionName = '등용';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        //NOTE: 사망 직전에 '등용' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if(!key_exists('destGeneralID', $this->arg)){
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        if(!is_int($destGeneralID)){
            return false;
        }
        if($destGeneralID <= 0){
            return false;
        }
        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }
        $this->arg = [
            'destGeneralID'=>$destGeneralID
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['nation'], 0);
        $this->setDestGeneral($destGeneral);

        [$reqGold, $reqRice] = $this->getCost();
        $relYear = $this->env['year'] - $this->env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::ReqEnvValue('join_mode', '==', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::DifferentNationDestGeneral(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

        if($this->destGeneralObj->getVar('level') == 12){
            $this->runnableConstraints[] = ConstraintHelper::AlwaysFail('군주에게는 등용장을 보낼 수 없습니다.');
        }
    }

    public function getCost():array{
        $env = $this->env;
        if(!$this->isArgValid){
            return [$env['develcost'], 0];
        }
        $destGeneral = $this->destGeneralObj;
        $reqGold = Util::round(
            $env['develcost'] +
            ($destGeneral->getVar('experience') + $destGeneral->getVar('dedication')) / 1000
        ) * 10;
        return [$reqGold, 0];
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

        $logger = $general->getLogger();

        $destGeneralName = $this->destGeneralObj->getName();
        $destGeneralID = $this->destGeneralObj->getID();
        

        $msg = ScoutMessage::buildScoutMessage($general->getID(), $destGeneralID, $reason, new \DateTime($general->getVar('turntime')));
        if($msg){
            $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 등용 권유 서신을 보냈습니다. <1>$date</>");
            $msg->send(true);
        }
        else{
            $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 등용 권유 서신을 보내지 못했습니다. {$reason} <1>$date</>");
        }

        $exp = 100;
        $ded = 200;

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        [$reqGold, $reqRice] = $this->getCost();

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('intel2', 1);
        $general->increaseVarWithLimit('gold', -$reqGold, 0);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);
        $this->destGeneralObj->applyDB($db);

        return true;
    }

    
}