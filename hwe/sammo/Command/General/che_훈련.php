<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use function \sammo\{
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;



class che_훈련 extends Command\GeneralCommand{
    static protected $actionName = '훈련';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
        ];
        
        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralCrew(),
            ConstraintHelper::ReqGeneralTrainMargin(GameConst::$maxTrainByCommand),
        ];

    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}(통솔경험";
        if($reqGold > 0){
            $title .= ", 자금{$reqGold}";
        }
        if($reqRice > 0){
            $title .= ", 군량{$reqRice}";
        }
        $title .= ')';
        return $title;
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
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $score = Util::round($general->getLeadership() * 100 / $general->getVar('crew') * GameConst::$trainDelta);
        $scoreText = number_format($score, 0);

        $sideEffect = Util::valueFit(intval($general->getVar('atmos') * GameConst::$atmosSideEffectByTraining), 0);

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("훈련치가 <C>$scoreText</> 상승했습니다. <1>$date</>");

        $exp = 100;
        $ded = 70;

        $general->increaseVarWithLimit('train', $score, 0, GameConst::$maxTrainByCommand);
        $general->setVar('atmos', $sideEffect);

        $general->addDex($general->getCrewTypeObj(), $score, false);

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}