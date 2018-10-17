<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use function \sammo\{
    uniqueItemEx
};

use \sammo\Constraint\Constraint;


class che_사기진작 extends Command\GeneralCommand{
    static protected $actionName = '사기진작';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ['NoNeutral'], 
            ['NoWanderingNation'],
            ['OccupiedCity'],
            ['ReqGeneralCrew'],
            ['ReqGeneralGold', $reqGold],
            ['ReqGeneralRice', $reqRice],
            ['ReqGeneralAtmosMargin'],
        ];

    }

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    public function getCost():array{
        return [Util::round($this->getVar('crew')/100), 0];
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

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $general->increaseVarWithLimit('atmos', $score, 0, GameConst::$maAtmosByCommand);
        $general->setVar('train', $sideEffect);

        $general->addDex($general->getCrewTypeObj(), $score, false);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('leader2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        uniqueItemEx($general->getID(), $logger);

        return true;
    }

    
}