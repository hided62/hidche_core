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

use function \sammo\tryUniqueItemLottery;

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
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::ReqGeneralAtmosMargin(GameConst::$maxAtmosByCommand),
        ];

    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        //[$reqGold, $reqRice] = $this->getCost();

        return "{$name}(통솔경험, 자금↓)";
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

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $score = Util::round($general->getLeadership() * 100 / $general->getVar('crew') * GameConst::$atmosDelta);
        $scoreText = number_format($score, 0);

        $sideEffect = Util::valueFit(intval($general->getVar('train') * GameConst::$trainSideEffectByAtmosTurn), 0);

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("사기치가 <C>$scoreText</> 상승했습니다. <1>$date</>");

        $exp = 100;
        $ded = 70;

        $general->increaseVarWithLimit('atmos', $score, 0, GameConst::$maxAtmosByCommand);
        $general->setVar('train', $sideEffect);

        $general->addDex($general->getCrewTypeObj(), $score, false);

        [$reqGold,] = $this->getCost();
        $general->increaseVarWithLimit('gold', -$reqGold, 0);

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);
        $general->applyDB($db);

        return true;
    }


}