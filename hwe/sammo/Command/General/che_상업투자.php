<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    RandUtil
};

use function \sammo\getDomesticExpLevelBonus;
use function \sammo\CriticalRatioDomestic;
use function \sammo\CriticalScoreEx;
use function \sammo\tryUniqueItemLottery;
use function \sammo\updateMaxDomesticCritical;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_상업투자 extends Command\GeneralCommand{
    static protected $cityKey = 'comm';
    static protected $statKey = 'intel';
    static protected $actionKey = '상업';
    static protected $actionName = '상업 투자';
    static protected $debuffFront = 0.5;

    protected $reqGold;

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
            ConstraintHelper::RemainCityCapacity(static::$cityKey, static::$actionName)
        ];

        $this->reqGold = $reqGold;
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
        $develCost = $this->env['develcost'];
        $reqGold = Util::round($this->generalObj->onCalcDomestic(static::$actionKey, 'cost', $develCost));
        $reqRice = 0;

        return [$reqGold, $reqRice];
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

    protected function calcBaseScore(RandUtil $rng):float{
        $trust = Util::valueFit($this->city['trust'], 50);
        $general = $this->generalObj;

        if(static::$statKey == 'intel'){
            $score = $general->getIntel(true, true, true, false);
        }
        else if(static::$statKey == 'strength'){
            $score = $general->getStrength(true, true, true, false);
        }
        else if(static::$statKey == 'leadership'){
            $score = $general->getLeadership(true, true, true, false);
        }
        else{
            throw new \sammo\MustNotBeReachedException();
        }

        $score *= $trust / 100;
        $score *= getDomesticExpLevelBonus($general->getVar('explevel'));
        $score *= $rng->nextRange(0.8, 1.2);
        $score = $general->onCalcDomestic(static::$actionKey, 'score', $score);

        return $score;
    }

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;

        $trust = Util::valueFit($this->city['trust'], 50);

        $score = Util::valueFit($this->calcBaseScore($rng), 1);

        ['success'=>$successRatio, 'fail'=>$failRatio] = CriticalRatioDomestic($general, static::$statKey);
        if($trust < 80){
            $successRatio *= $trust / 80;
        }
        $successRatio = $general->onCalcDomestic(static::$actionKey, 'success', $successRatio);
        $failRatio = $general->onCalcDomestic(static::$actionKey, 'fail', $failRatio);

        $successRatio = Util::valueFit($successRatio, 0, 1);
        $failRatio = Util::valueFit($failRatio, 0, 1 - $successRatio);
        $normalRatio = 1 - $failRatio - $successRatio;

        $pick = $rng->choiceUsingWeight([
            'fail'=>$failRatio,
            'success'=>$successRatio,
            'normal'=>$normalRatio
        ]);

        $logger = $general->getLogger();

        $date = $general->getTurnTime($general::TURNTIME_HM);

        $score *= CriticalScoreEx($rng, $pick);
        $score = Util::round($score);

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        if($pick == 'success'){
            updateMaxDomesticCritical($general, $score);
        }
        else{
            $general->setAuxVar('max_domestic_critical', 0);
        }

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

        if(in_array($this->city['front'], [1, 3])){
            $debuffFront = static::$debuffFront;

            if($this->nation['capital'] == $this->city['city']){
                $gameStor = \sammo\KVStorage::getStorage($db, 'game_env');
                [$year, $startYear] = $gameStor->getValuesAsArray(['year', 'startyear']);
                $relYear = $year - $startYear;

                if($relYear < 25){
                    $debuffScale = Util::clamp($relYear - 5, 0, 20) * 0.05;
                    $debuffFront = ($debuffScale * $debuffFront) + (1 - $debuffScale);
                }
            }

            $score *= $debuffFront;
        }

        //NOTE: 내정량 상승시 초과 가능?
        $cityUpdated = [
            static::$cityKey => Util::valueFit(
                $this->city[static::$cityKey] + $score,
                0,
                $this->city[static::$cityKey.'_max']
            )
        ];
        $db->update('city', $cityUpdated, 'city=%i', $general->getVar('city'));

        $general->increaseVarWithLimit('gold', -$this->reqGold, 0);
        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar(static::$statKey.'_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);
        $general->applyDB($db);

        return true;
    }
}