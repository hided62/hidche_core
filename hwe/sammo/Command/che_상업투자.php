<?php
namespace sammo\Command;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    getGeneralLeadership,getGeneralPower,getGeneralIntel,
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, CriticalScore,
    checkAbilityEx
};

use \sammo\Constraint\Constraint;
use function sammo\CriticalScore;
use function sammo\uniqueItemEx;
use function sammo\getGeneralLeadership;


class che_상업투자 extends BaseCommand{
    static $cityKey = 'comm';
    static $statKey = 'intel';
    static $actionKey = '상업';
    static $actionName = '상업 투자';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        $develCost = $this->env['develcost'];
        $reqGold = $general->onCalcDomestic(static::$actionKey, 'cost', $reqGold);

        $this->constraints=[
            ['NoNeutral'], 
            ['NoWanderingNation'],
            ['OccupiedCity'],
            ['SuppliedCity'],
            ['ReqGeneralGold', $reqGold],
            ['RemainCityCapacity', [static::$cityKey, static::$actionName]]
        ];

        $this->reqGold = $reqGold;
    }

    protected function calcBaseScore():float{
        $general = $this->generalObj;

        if(static::$statKey == 'intel'){
            $score = getGeneralIntel($general->getRaw(), true, true, true, false);
        }
        else if(static::$statKey == 'power'){
            $score = getGeneralPower($general->getRaw(), true, true, true, false);
        }
        else if(static::$statKey == 'leader'){
            $score = getGeneralLeadership($general->getRaw(), true, true, true, false);
        }
        else{
            throw new \sammo\MustNotBeReachedException();
        }
        
        $score *= $trust / 100;
        $score *= getDomesticExpLevelBonus($general['explevel']);
        $score *= Util::randRange(0.8, 1.2);
        $score = $general->onCalcDomestic(static::$actionKey, 'score', $score);

        return $score;
    }

    public function run():bool{
        if(!$this->isAvailable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;

        $trust = Util::valueFit($this->city['trust'], 50);

        $score = $this->calcBaseScore();

        ['succ'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general->getRaw(), static::$statKey);
        $successRatio = $general->onCalcDomestic(static::$cityKey, 'succ', $successRatio);
        $failRatio = $general->onCalcDomestic(static::$cityKey, 'fail', $failRatio);

        $failRatio = Util::valueFit($failRatio, 0, 1);
        $successRatio = Util::valueFit($successRatio, 0, 1 - $failRatio);
        $normalRatio = 1 - $failRatio - $successRatio;

        $pick = Util::choiceRandomUsingWeight([
            'fail'=>$failRatio, 
            'succ'=>$successRatio, 
            'normal'=>$normalRatio
        ]);

        $logger = $general->getLogger();

        $date = substr($general->getVar('turntime'),11,5);

        $josaUl = JosaUtil::pick(static::$actionName, '을');
        if($pick == 'fail'){
            $score = CriticalScore($score, 1);
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} <span class='ev_failed'>실패</span>하여 <C>$score</> 상승했습니다. <1>$date</>");
        }
        else if($pick == 'succ'){
            $score = CriticalScore($score, 1);
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>");
        }
        else{
            $score = Util::round($score);
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} 하여 <C>$score</> 상승했습니다. <1>$date</>");
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        //NOTE: 내정량 상승시 초과 가능?
        $cityUpdated = [
            static::$cityKey => Util::valueFit(
                $this->city[static::$cityKey] + $score,
                0,
                $this->city[static::$cityKey.'2']
            )
        ];

        $general->increaseVarWithLimit('gold', -$this->reqGold, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar(static::$statKey.'2', 1);
        $general->updateVar('resturn', 'SUCCESS');
        $general->applyDB($db);

        $this->checkStatChange();
        uniqueItemEx($general->getVar('no'), $logger);
    }

    
}