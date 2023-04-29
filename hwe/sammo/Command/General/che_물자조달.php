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
use function \sammo\CriticalScoreEx;
use function sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_물자조달 extends Command\GeneralCommand{
    static protected $actionName = '물자조달';
    static protected $debuffFront = 0.5;

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity()
        ];

    }

    public function getCommandDetailTitle():string{
        return "{$this->getName()}(랜덤경험)";
    }

    public function getCost():array{
        return [0, 0];
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

        [$resName, $resKey] = $rng->choice([
            ['금', 'gold'],
            ['쌀', 'rice']
        ]);

        $score = $general->getLeadership() + $general->getStrength() + $general->getIntel();
        $score *= getDomesticExpLevelBonus($general->getVar('explevel'));
        $score *= $rng->nextRange(0.8, 1.2);

        $successRatio = 0.1;
        $failRatio = 0.3;

        $successRatio = $general->onCalcDomestic('조달', 'success', $successRatio);
        $failRatio = $general->onCalcDomestic('조달', 'fail', $failRatio);
        $normalRatio = 1 - $failRatio - $successRatio;

        $pick = $rng->choiceUsingWeight([
            'fail'=>$failRatio,
            'success'=>$successRatio,
            'normal'=>$normalRatio
        ]);
        $score *= CriticalScoreEx($rng, $pick);
        $score = $general->onCalcDomestic('조달', 'score', $score);

        $score = Util::round($score);

        $exp = $score * 0.7 / 3;
        $ded = $score * 1.0 / 3;

        $logger = $general->getLogger();

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

        $scoreText = number_format($score, 0);


        if($pick == 'fail'){
            $logger->pushGeneralActionLog("조달을 <span class='ev_failed'>실패</span>하여 {$resName}을 <C>$scoreText</> 조달했습니다. <1>$date</>");
        }
        else if($pick == 'success'){
            $logger->pushGeneralActionLog("조달을 <S>성공</>하여 {$resName}을 <C>$scoreText</> 조달했습니다. <1>$date</>");
        }
        else{
            $logger->pushGeneralActionLog("{$resName}을 <C>$scoreText</> 조달했습니다. <1>$date</>");
        }

        $incStat = $rng->choiceUsingWeight([
            'leadership_exp'=>$general->getLeadership(false, false, false, false),
            'strength_exp'=>$general->getStrength(false, false, false, false),
            'intel_exp'=>$general->getIntel(false, false, false, false)
        ]);

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar($incStat, 1);

        $db->update('nation', [
            $resKey=>$db->sqleval('%b + %i', $resKey, $score)
        ], 'nation=%i',$general->getNationID());

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);

        $general->applyDB($db);

        return true;
    }


}