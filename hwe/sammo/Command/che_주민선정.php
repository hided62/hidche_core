<?php
namespace sammo\Command;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    getGeneralLeadership,getGeneralPower,getGeneralIntel,
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, CriticalScore,
    uniqueItemEx
};

use \sammo\Constraint\Constraint;
use function sammo\CriticalScore;
use function sammo\uniqueItemEx;
use function sammo\getGeneralLeadership;


class che_주민선정 extends BaseCommand{
    static $cityKey = 'trust';
    static $statKey = 'leader';
    static $actionKey = '민심';
    static $actionName = '주민 선정';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        $develCost = $this->env['develcost'] * 2;
        $reqRice = $general->onCalcDomestic(static::$actionKey, 'cost', $reqGold);

        $this->constraints=[
            ['NoNeutral'], 
            ['NoWanderingNation'],
            ['OccupiedCity'],
            ['SuppliedCity'],
            ['ReqGeneralRice', $reqRice],
            ['RemainCityTrust', static::$actionName]
        ];

        $this->reqRice = $reqRice;
    }

    protected function calcBaseScore():float{
        $general = $this->generalObj;

        if(static::$statKey == 'leader'){
            $score = getGeneralLeadership($general->getRaw(), true, true, true, false);
        }
        else{
            throw new \sammo\MustNotBeReachedException();
        }
        
        $score /= 10;
        $score *= getDomesticExpLevelBonus($general['explevel']);
        $score *= Util::randRange(0.8, 1.2);
        $score = $general->onCalcDomestic(static::$actionKey, 'score', $score);
        $score = Util::valutFit($score, 1);

        return $score;
    }

    public function run():bool{
        if(!$this->isAvailable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;

        $score = Util::valueFit($this->calcBaseScore(), 1);

        ['success'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general->getRaw(), static::$statKey);
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
        $scoreText = number_format($score, 1);

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

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        //NOTE: 내정량 상승시 초과 가능?
        $cityUpdated = [
            static::$cityKey => Util::valueFit(
                $this->city[static::$cityKey] + $score,
                0,
                100
            )
        ];
        $db->update('city', $cityUpdated, 'city=%i', $general->getVar('city'));

        $general->increaseVarWithLimit('rice', -$this->reqRice, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar(static::$statKey.'2', 1);
        $general->updateVar('resturn', 'SUCCESS');
        $general->applyDB($db);

        $this->checkStatChange();
        uniqueItemEx($general->getVar('no'), $logger);
    }

    
}