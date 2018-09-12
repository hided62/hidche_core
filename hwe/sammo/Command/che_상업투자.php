<?php
namespace sammo\Command;

use \sammo\{
    Util, JosaUtil,
    General, 
    ActionLogger,
    getGeneralIntel,
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, CriticalScore,
    checkAbilityEx
};

use \sammo\Constraint\Constraint;
use function sammo\CriticalScore;


class che_상업투자 extends BaseCommand{
    static $cityKey = 'comm';
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

    public function run():bool{
        if(!$this->isAvailable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $general = $this->generalObj;

        $trust = Util::valueFit($this->city['trust'], 50);

        $score = getGeneralIntel($general->getRaw(), true, true, true, false);
        $score *= $trust / 100;
        $score *= getDomesticExpLevelBonus($general['explevel']);
        $score *= Util::randRange(0.8, 1.2);
        $score = $general->onCalcDomestic(static::$actionKey, 'score', $score);

        ['succ'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general->getRaw(), 2);
        $successRatio = $naionTypeObj->onCalcDomestic(static::$cityKey, 'succ', $successRatio);
        $failRatio = $naionTypeObj->onCalcDomestic(static::$cityKey, 'fail', $failRatio);

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
            $logger->pushGeneralActionLog(static::$actionName."{$josaUl} 하여 <C>$score</> 상승했습니다. <1>$date</>");
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        //TODO: 내정량 상승시 초과 가능?

        $general->increaseVarWithLimit('gold', -$this->reqGold, 0);


        //TODO:uniqueItemEx에 해당하는 함수 추가
    }

    
}