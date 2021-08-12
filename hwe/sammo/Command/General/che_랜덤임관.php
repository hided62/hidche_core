<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\TimeUtil;
use \sammo\Json;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\GameUnitConst;
use \sammo\LastTurn;
use \sammo\Command;

use function \sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use sammo\MustNotBeReachedException;



class che_랜덤임관 extends Command\GeneralCommand
{
    static protected $actionName = '랜덤임관';

    protected function argTest(): bool
    {
        $this->arg = null;
        return true;

        //TODO: 구현
        /*if($this->arg === null){
            return true;
        }
        
        $destNationIDList = $this->arg['destNationIDList']??null;
        //null은 에러, []는 정상

        if($destNationIDList === null || !is_array($destNationIDList)){
            return true;
        }
        if($destNationIDList && Util::isDict($destNationIDList)){
            return false;
        }
        foreach($destNationIDList as $nationID){
            if(!is_int($nationID)){
                return false;
            }
            if($nationID < 1){
                return false;
            }
        }
        $this->arg = [
            'destNationIDList' => $destNationIDList
        ];    
        
        return true;*/
    }

    protected function init()
    {

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $relYear = $env['year'] - $env['startyear'];

        $this->fullConditionConstraints = [
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::AllowJoinAction(),
        ];

        /*
        if($this->arg['destNationIDList']??false){
            $this->fullConditionConstraints[] = ConstraintHelper::ExistsAllowJoinNation($relYear, $this->arg['destNationIDList']);
        }
        */
    }

    public function getCommandDetailTitle(): string
    {
        return '무작위 국가로 임관';
    }

    public function getBrief(): string
    {
        return '무작위 국가로 임관';
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $relYear = $env['year'] - $env['startyear'];

        /*
        $notIn = array_merge($general->getAuxVar('joinedNations')??[], $this->arg['destNationIDList']);
        */
        $notIn = null;

        $destNation = null;

        if ($general->getNPCType() >= 2 && !$env['fiction'] && 1000 <= $env['scenario'] && $env['scenario'] < 2000) {
            if ($notIn) {
                $nations = $db->query(
                    'SELECT nation.`name` as `name`,nation.nation as nation,scout,gennum,`affinity` FROM nation join general on general.nation = nation.nation and general.officer_level = 12 WHERE scout=0 and gennum<%i and nation.nation not in %li',
                    $relYear < 3 ? GameConst::$initialNationGenLimit : GameConst::$defaultMaxGeneral,
                    $notIn
                );
            } else {
                $nations = $db->query(
                    'SELECT nation.`name` as `name`,nation.nation as nation,scout,gennum,`affinity` FROM nation join general on general.nation = nation.nation and general.officer_level = 12 WHERE scout=0 and gennum<%i',
                    $relYear < 3 ? GameConst::$initialNationGenLimit : GameConst::$defaultMaxGeneral
                );
            }

            shuffle($nations);

            $allGen = Util::arraySum($nations, 'gennum');

            $maxScore = 1 << 30;

            $affinity = $general->getVar('affinity');

            foreach ($nations as $testNation) {
                $affinity = abs($affinity - $testNation['affinity']);
                $affinity = min($affinity, abs($affinity - 150));

                $score = log($affinity + 1, 2); //0~

                //쉐킷쉐킷
                $score += Util::randF();

                $score += sqrt($testNation['gennum'] / $allGen);

                if ($score < $maxScore) {
                    $maxScore = $score;
                    $destNation = $testNation;
                }
            }
        } else {
            $onlyRandom = $env['join_mode'] == 'onlyRandom';
            $genLimit = GameConst::$defaultMaxGeneral;
            if ($onlyRandom && TimeUtil::IsRangeMonth($env['init_year'], $env['init_month'], 1, $env['year'], $env['month'])) {
                $genLimit = GameConst::$initialNationGenLimitForRandInit;
            } else if ($relYear < 3) {
                $genLimit = GameConst::$initialNationGenLimit;
            }

            $generalsCnt = [];
            if ($notIn) {
                $rawGeneralsCnt = $db->query(
                    'SELECT general.nation as nation, nation.gennum, nation.name, npc, count(*) as cnt FROM general JOIN nation ON general.nation = nation.nation WHERE npc != 9 AND nation.gennum < %i AND nation.scout=0 AND nation.nation NOT IN %li GROUP BY general.nation, general.npc',
                    $genLimit,
                    $notIn
                );
            } else {
                $rawGeneralsCnt = $db->query(
                    'SELECT general.nation as nation, nation.gennum, nation.name, npc, count(*) as cnt FROM general JOIN nation ON general.nation = nation.nation WHERE npc != 9  AND nation.gennum < %i AND nation.scout=0 GROUP BY general.nation, general.npc',
                    $genLimit
                );
            }


            foreach ($rawGeneralsCnt as $nation) {
                $nationID = $nation['nation'];
                if (!\key_exists($nationID, $generalsCnt)) {
                    $generalsCnt[$nationID] = [
                        'nation' => $nationID,
                        'gennum' => $nation['gennum'],
                        'name' => $nation['name'],
                        'cnt' => 0,
                    ];
                    $generalsCnt[$nationID]['cnt'] = 0;
                }

                if ($nation['npc'] <= 2) {
                    $calcCnt = $nation['cnt'];
                } else {
                    $calcCnt = $nation['cnt'] / 2;
                }

                if ($general->getNPCType() < 2 && Util::starts_with($nation['name'], 'ⓤ')) {
                    $calcCnt *= 100;
                }

                $generalsCnt[$nationID]['cnt'] += $calcCnt;
            }

            $randVals = [];
            foreach ($generalsCnt as $testNation) {
                $randVals[] = [$testNation, 1 / $testNation['cnt']];
            }

            if ($randVals) {
                $destNation = Util::choiceRandomUsingWeightPair($randVals);
            }
        }

        $logger = $general->getLogger();

        if (!$destNation) {
            //임관 가능한 국가가 없다!
            $logger->pushGeneralActionLog("임관 가능한 국가가 없습니다. <1>$date</>");
            $this->alternative = new che_요양($general, $this->env, null);
            return false;
        }

        $gennum = $destNation['gennum'];
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];



        $talkList = [
            '어쩌다 보니',
            '인연이 닿아',
            '발길이 닿는 대로',
            '소문을 듣고',
            '점괘에 따라',
            '천거를 받아',
            '유명한',
            '뜻을 펼칠 곳을 찾아',
            '고향에 가까운',
            '천하의 균형을 맞추기 위해',
            '오랜 은거를 마치고',
        ];
        $randomTalk = Util::choiceRandom($talkList);

        $logger->pushGeneralActionLog("<D>{$destNationName}</>에 랜덤 임관했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>에 랜덤 임관");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} {$randomTalk} <D><b>{$destNationName}</b></>에 <S>임관</>했습니다.");

        if ($gennum < GameConst::$initialNationGenLimit) {
            $exp = 700;
        } else {
            $exp = 100;
        }

        $general->setVar('nation', $destNationID);
        $general->setVar('officer_level', 1);
        $general->setVar('officer_city', 0);
        $general->setVar('belong', 1);

        if ($this->destGeneralObj !== null) {
            $general->setVar('city', $this->destGeneralObj->getCityID());
        } else {
            $targetCityID = $db->queryFirstField('SELECT city FROM general WHERE nation = %i AND officer_level=12', $destNationID);
            $general->setVar('city', $targetCityID);
        }

        $db->update('nation', [
            'gennum' => $db->sqleval('gennum + 1')
        ], 'nation=%i', $destNationID);

        $relYear = $env['year'] - $env['startyear'];
        if ($general->getNPCType() == 1 || $relYear >= 3) {
            $joinedNations = $general->getAuxVar('joinedNations') ?? [];
            $joinedNations[] = $destNationID;
            $general->setAuxVar('joinedNations', $joinedNations);
        }

        $general->addExperience($exp);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general, '랜덤 임관');
        $general->applyDB($db);

        return true;
    }
}
