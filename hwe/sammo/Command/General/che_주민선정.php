<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function \sammo\getDomesticExpLevelBonus;
use function \sammo\CriticalRatioDomestic;
use function \sammo\CriticalScoreEx;
use function \sammo\tryUniqueItemLottery;
use function \sammo\updateMaxDomesticCritical;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_주민선정 extends Command\GeneralCommand{
    static protected $cityKey = 'trust';
    static protected $statKey = 'leadership';
    static protected $actionKey = '민심';
    static protected $actionName = '주민 선정';

    protected $reqRice;

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::RemainCityTrust(static::$actionName)
        ];

        $this->reqRice = $reqRice;
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        $statTypeBase = [
            'leadership'=>'통솔경험',
            'strength'=>'무력경험',
            'intel'=>'지력경험',
        ];
        $statType = $statTypeBase[static::$statKey];
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}({$statType}";
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
        $develCost = $this->env['develcost'] * 2;
        $reqGold = 0;
        $reqRice = $this->generalObj->onCalcDomestic(static::$actionKey, 'cost', $develCost);
        
        return [$reqGold, Util::round($reqRice)];
    }

    public function getCompensationStyle():?int{
        return $this->generalObj->onCalcDomestic(static::$actionKey, 'score', 100)<=>100;
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    protected function calcBaseScore():float{
        $general = $this->generalObj;

        if(static::$statKey == 'leadership'){
            $score = $general->getLeadership(true, true, true, false);
        }
        else{
            throw new \sammo\MustNotBeReachedException();
        }
        
        $score *= getDomesticExpLevelBonus($general->getVar('explevel'));
        $score *= Util::randRange(0.8, 1.2);
        $score = $general->onCalcDomestic(static::$actionKey, 'score', $score);
        $score = Util::valueFit($score, 1);

        return $score;
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;

        $score = Util::valueFit($this->calcBaseScore(), 1);

        ['success'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general, static::$statKey);
        $successRatio = $general->onCalcDomestic(static::$actionKey, 'success', $successRatio);
        $failRatio = $general->onCalcDomestic(static::$actionKey, 'fail', $failRatio);

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

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        if($pick == 'success'){
            updateMaxDomesticCritical($general, $score);   
        }
        else{
            $general->setAuxVar('max_domestic_critical', 0);
        }

        $score /= 10;

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
        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar(static::$statKey.'_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}