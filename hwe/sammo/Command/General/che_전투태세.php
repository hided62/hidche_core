<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command,
    MustNotBeReachedException
};


use function \sammo\{
    uniqueItemEx, getTechCost
};

use \sammo\Constraint\Constraint;


class che_전투태세 extends Command\GeneralCommand{
    static protected $actionName = '전투태세';

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
            ['NoNeutral'], 
            ['NoWanderingNation'],
            ['OccupiedCity'],
            ['ReqGeneralCrew'],
            ['ReqGeneralGold', $reqGold],
            ['ReqGeneralRice', $reqRice],
            ['ReqGeneralTrainMargin', GameConst::$maxTrainByCommand - 10],
            ['ReqGeneralAtmosMargin', GameConst::$maxAtmosByCommand - 10],
        ];

    }

    public function getCost():array{
        $crew = $this->getVar('crew');
        $techCost = getTechCost($this->nation['tech']);
        return [Util::round($crew / 100 * 3 * $techCost), 0];
    }
    
    public function getPreReqTurn():int{
        return 3;
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

        $lastTurn = $general->getLastTurn();
        $turnResult = new LastTurn(static::getName(), $this->arg);

        $reqTurn = $this->getPreReqTurn();

        if($lastTurn->getCommand() != static::getName()){
            $turnResult->setTerm(1);
        }
        else if($lastTurn->getTerm() == $reqTurn){
            $turnResult->setTerm(1);
        }
        else if($lastTurn->getTerm() < $reqTurn){
            $turnResult->setTerm($lastTurn->getTerm()+1);
        }
        else{
            throw new MustNotBeReachedException('전투 태세에 올바른 턴이 아님');
        }

        $term = $turnResult->getTerm();


        $logger = $general->getLogger();

        if($term < 3){
            $logger->pushGeneralActionLog("병사들을 열심히 훈련중... ({$term}/3) <1>$date</>");
            $general->setResultTurn($turnResult);
            $general->applyDB($db);

            return true;
        }
        
        $logger->pushGeneralActionLog("전투태세 완료! ({$term}/3) <1>$date</>");

        $general->increaseVarWithLimit('train', 0, GameConst::$maxTrainByCommand - 5); //95보다 높으면 '깎이지는 않음'
        $general->increaseVarWithLimit('atmos', 0, GameConst::$maxAtmosByCommand - 5);

        $exp = 100 * 3;
        $ded = 70 * 3;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);

        $crew = $general->getVar('crew');

        $general->addDex($general->getCrewTypeObj(), $crew / 100 * 3, false);
        
        $general->increaseVar('leader2', 3);
        $general->setResultTurn($turnResult);
        $general->checkStatChange();
        $general->applyDB($db);

        uniqueItemEx($general->getID(), $logger);

        return true;
    }

    
}