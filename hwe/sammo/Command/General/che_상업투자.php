<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
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
    uniqueItemEx
};

use \sammo\Constraint\Constraint;


class che_상업투자 extends Command\GeneralCommand{
    static protected $cityKey = 'comm';
    static protected $statKey = 'intel';
    static protected $actionKey = '상업';
    static protected $actionName = '상업 투자';
    static protected $debuffFront = 0.5;

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        [$reqGold, $reqRice] = $this->getCost();

        $this->runnableConstraints=[
            ['NoNeutral'], 
            ['NoWanderingNation'],
            ['OccupiedCity'],
            ['SuppliedCity'],
            ['ReqGeneralGold', $reqGold],
            ['ReqGeneralRice', $reqRice],
            ['RemainCityCapacity', [static::$cityKey, static::$actionName]]
        ];

        $this->reqGold = $reqGold;
    }

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    public function getCost():array{
        $develCost = $this->env['develcost'];
        $reqGold = $general->onCalcDomestic(static::$actionKey, 'cost', $develCost);
        $reqRice = 0;
        
        return [$reqGold, $reqRice];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    protected function calcBaseScore():float{
        $trust = Util::valueFit($this->city['trust'], 50);
        $general = $this->generalObj;

        if(static::$statKey == 'intel'){
            $score = $general->getIntel(true, true, true, false);
        }
        else if(static::$statKey == 'power'){
            $score = $general->getPower(true, true, true, false);
        }
        else if(static::$statKey == 'leader'){
            $score = $general->getLeadership(true, true, true, false);
        }
        else{
            throw new \sammo\MustNotBeReachedException();
        }
        
        $score *= $trust / 100;
        $score *= getDomesticExpLevelBonus($general->getVar('explevel'));
        $score *= Util::randRange(0.8, 1.2);
        $score = $general->onCalcDomestic(static::$actionKey, 'score', $score);

        return $score;
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;

        $trust = Util::valueFit($this->city['trust'], 50);

        $score = Util::valueFit($this->calcBaseScore(), 1);

        ['success'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general->getRaw(), static::$statKey);
        if($trust < 80){
            $successRatio *= $trust / 80;
        }
        $successRatio = $general->onCalcDomestic(static::$cityKey, 'success', $successRatio);
        $failRatio = $general->onCalcDomestic(static::$cityKey, 'fail', $failRatio);

        $successRatio = Util::valueFit($successRatio, 0, 1);
        $failRatio = Util::valueFit($failRatio, 0, 1 - $successRatio);
        $normalRatio = 1 - $failRatio - $successRatio;

        $pick = Util::choiceRandomUsingWeight([
            'fail'=>$failRatio, 
            'success'=>$successRatio, 
            'normal'=>$normalRatio
        ]);

        $logger = $general->getLogger();

        $date = substr($general->getVar('turntime'),11,5);

        $score *= CriticalScoreEx($pick);
        $score = Util::round($score);

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        $scoreText = number_format($score, 0);

        $josaUl = JosaUtil::pick(static::$actionName, '을');
        if($pick == 'fail'){
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} <span class='ev_failed'>실패</span>하여 <C>$scoreText</> 상승했습니다. <1>$date</>");
        }
        else if($pick == 'success'){
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} <S>성공</>하여 <C>$scoreText</> 상승했습니다. <1>$date</>");
        }
        else{
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} 하여 <C>$scoreText</> 상승했습니다. <1>$date</>");
        }

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        if(in_array($this->city['front'], [1, 3]) && $this->nation['capital'] != $this->city['city']){
            $score *= static::$debuffFront;
        }

        //NOTE: 내정량 상승시 초과 가능?
        $cityUpdated = [
            static::$cityKey => Util::valueFit(
                $this->city[static::$cityKey] + $score,
                0,
                $this->city[static::$cityKey.'2']
            )
        ];
        $db->update('city', $cityUpdated, 'city=%i', $general->getVar('city'));

        $general->increaseVarWithLimit('gold', -$this->reqGold, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar(static::$statKey.'2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);
        
        uniqueItemEx($general->getID(), $logger);
    }

    
}