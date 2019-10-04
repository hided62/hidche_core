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


class che_사기진작 extends Command\GeneralCommand{
    static protected $actionName = '사기진작';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralCrew(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::ReqGeneralAtmosMargin(GameConst::$maxAtmosByCommand),
        ];

    }

    public function getCost():array{
        $general = $this->generalObj;
        return [Util::round($general->getVar('crew')/100), 0];
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

        $score = Util::round($general->getLeadership() * 100 / $general->getVar('crew') * GameConst::$atmosDelta);
        $scoreText = number_format($score, 0);

        $sideEffect = Util::valueFit(intval($general->getVar('train') * GameConst::$trainSideEffectByAtmosTurn), 0);

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("사기치가 <C>$scoreText</> 상승했습니다. <1>$date</>");

        $exp = 100;
        $ded = 70;

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $general->increaseVarWithLimit('atmos', $score, 0, GameConst::$maxAtmosByCommand);
        $general->setVar('train', $sideEffect);

        $general->addDex($general->getCrewTypeObj(), $score, false);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('leadership2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}