<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    CityConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_발령 extends Command\NationCommand{
    static protected $actionName = '발령';
    static public $reqArg = true;

    protected function argTest():bool{
        //NOTE: 사망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if(!key_exists('destGeneralID', $this->arg)){
            return false;
        }
        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(!key_exists($this->arg['destCityID'], CityConst::all())){
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        $destCityID = $this->arg['destCityID'];

        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }
        $this->arg = [
            'destGeneralID'=>$destGeneralID,
            'destCityID'=>$destCityID,
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->setDestCity($this->arg['destCityID'], null);

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], null, 1);
        $this->setDestGeneral($destGeneral);
        
        $this->runnableConstraints=[
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral(),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::SuppliedDestCity(),
        ];
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testRunnable();
        if($failReason === null){
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destGeneralName = $this->destGeneralObj->getName();
        return "{$failReason} <Y>{$destGeneralName}</> {$commandName} 실패.";
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
        $generalName = $general->getName();
        $date = substr($general->getVar('turntime'),11,5);

        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $destGeneral = $this->destGeneralObj;
        $destGeneralName = $destGeneral->getName();
        
        $logger = $general->getLogger();

        $destGeneral->setVar('city', $destCityID);

        $josaUl = JosaUtil::pick($destGeneralName, '을');
        $josaRo = JosaUtil::pick($destCityName, '로');
        $destGeneral->getLogger()->pushGeneralActionLog("<Y>{$generalName}</>에 의해 <G><b>{$destCityName}</b></>{$josaRo} 발령됐습니다. <1>$date</>");
        $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>{$josaUl} <G><b>{$destCityName}</b></>{$josaRo} 발령했습니다. <1>$date</>");

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }
}