<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    LastTurn,
    Command, GameConst
};

use function sammo\{
    TechLimit,
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_기술연구 extends che_상업투자{
    static protected $statKey = 'intel';
    static protected $actionKey = '기술';
    static protected $actionName = '기술 연구';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['tech']);
        
        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice)
        ];

        $this->reqGold = $reqGold;
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;

        $trust = Util::valueFit($this->city['trust'], 50);

        $score = Util::valueFit($this->calcBaseScore(), 1);

        ['success'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general, static::$statKey);
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

        $date = $general->getTurnTime($general::TURNTIME_HM);

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

        if(TechLimit($this->env['startyear'], $this->env['year'], $this->nation['tech'])){
            $score /= 4;
        }

        $genCount = Util::valueFit(
            $db->queryFirstField('SELECT gennum FROM nation WHERE nation=%i', $general->getVar('nation')),
            GameConst::$initialNationGenLimit
        );

        $nationUpdated = [
            'tech' => $this->nation['tech'] + $score/$genCount
        ];
        $db->update('nation', $nationUpdated, 'nation=%i', $general->getVar('nation'));

        $general->increaseVarWithLimit('gold', -$this->reqGold, 0);
        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar(static::$statKey.'_exp', 1);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}