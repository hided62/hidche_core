<?php

namespace sammo\Command\General;

use\sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function\sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic,
    CriticalScoreEx,
    tryUniqueItemLottery,
    getAllNationStaticInfo
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_인재탐색 extends Command\GeneralCommand
{
    static protected $actionName = '인재탐색';

    protected function argTest(): bool
    {
        $this->arg = null;
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setNation();
        $env = $this->env;

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints = [
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $db = DB::db();
        $env = $this->env;

        $maxGenCnt = $env['maxgeneral'];
        $totalGenCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE npc <= 2');
        $totalNpcCnt = $db->queryFirstField('SELECT count(`no`) FROM general WHERE 3 <= npc AND npc <= 4');

        $name = $this->getName();
        [$reqGold, $reqRice] = $this->getCost();

        $foundProp = $this->calcFoundProp($maxGenCnt, $totalGenCnt, $totalNpcCnt);
        $foundPropText = number_format($foundProp * 100, 1);

        $title = "{$name}(랜덤경험";
        if ($reqGold > 0) {
            $title .= ", 자금{$reqGold}";
        }
        if ($reqRice > 0) {
            $title .= ", 군량{$reqRice}";
        }

        $title .= ", 확률 {$foundPropText}%)";
        return $title;
    }

    public function getCost(): array
    {
        return [$this->env['develcost'], 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function calcFoundProp(int $maxGenCnt, int $totalGenCnt, int $totalNpcCnt): float
    {


        $currCnt  = Util::toInt($totalGenCnt + $totalNpcCnt / 2);
        $remainSlot = $maxGenCnt - $currCnt;
        if ($remainSlot < 0) {
            $remainSlot = 0;
        }

        $foundPropMain = pow($remainSlot / $maxGenCnt, 6);
        $foundPropSmall = 1 / ($totalNpcCnt / 3 + 1);
        $foundPropBig = 1 / $maxGenCnt;

        if ($totalNpcCnt < 50) {
            $foundProp = max($foundPropMain, $foundPropSmall);
        } else {
            $foundProp = max($foundPropMain, $foundPropBig);
        }
        return $foundProp;
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nationID = $general->getNationID();

        $nationCnt = count(getAllNationStaticInfo());

        $totalGenCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE npc <= 2');
        $totalNpcCnt = $db->queryFirstField('SELECT count(`no`) FROM general WHERE 3 <= npc AND npc <= 4');

        $genCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc < 2', $nationID);
        $npcCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND 3 <= npc AND npc <= 4', $nationID);

        $currCnt  = Util::toInt($totalGenCnt + $totalNpcCnt / 2);
        $avgCnt = $currCnt / $nationCnt;

        $foundNpc = Util::randBool($this->calcFoundProp($env['maxgeneral'], $totalGenCnt, $totalNpcCnt));

        $logger = $general->getLogger();

        if (!$foundNpc) {
            $logger->pushGeneralActionLog("인재를 찾을 수 없었습니다. <1>$date</>");

            $incStat = Util::choiceRandomUsingWeight([
                'leadership_exp' => $general->getLeadership(false, false, false, false),
                'strength_exp' => $general->getStrength(false, false, false, false),
                'intel_exp' => $general->getIntel(false, false, false, false)
            ]);
            [$reqGold, $reqRice] = $this->getCost();

            $exp = 100;
            $ded = 70;

            $general->increaseVarWithLimit('gold', -$reqGold, 0);
            $general->increaseVarWithLimit('rice', -$reqRice, 0);
            $general->addExperience($exp);
            $general->addDedication($ded);
            $general->increaseVar($incStat, 1);
            $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
            $general->checkStatChange();
            tryUniqueItemLottery($general);
            $general->applyDB($db);
            return true;
        }
        //인간적으로 너무 길어서 끊었다!

        $exp = 200;
        $ded = 300;

        $pickTypeList = ['무' => 6, '지' => 6, '무지' => 3];

        $pickType = Util::choiceRandomUsingWeight($pickTypeList);

        $totalStat = GameConst::$defaultStatNPCTotal;
        $minStat = GameConst::$defaultStatNPCMin;
        $mainStat = GameConst::$defaultStatNPCMax - Util::randRangeInt(0, GameConst::$defaultStatNPCMin);
        $otherStat = $minStat + Util::randRangeInt(0, Util::toInt(GameConst::$defaultStatNPCMin/2));
        $subStat = $totalStat - $mainStat - $otherStat;
        if ($subStat < $minStat) {
            $subStat = $otherStat;
            $otherStat = $minStat;
            $mainStat = $totalStat - $subStat - $otherStat;
            if ($mainStat) {
                throw new \LogicException('기본 스탯 설정값이 잘못되어 있음');
            }
        }

        $avgGen = $db->queryFirstRow(
            'SELECT avg(dedication) as ded,avg(experience) as exp,
            avg(dex1+dex2+dex3+dex4) as dex_t, avg(age) as age, avg(dex5) as dex5
            from general where npc < 5',
            $nationID
        );
        $dexTotal = $avgGen['dex_t'];

        if ($pickType == '무') {
            $leadership = $subStat;
            $strength = $mainStat;
            $intel = $otherStat;
            $dexVal = Util::choiceRandom([
                [$dexTotal * 5 / 8, $dexTotal / 8, $dexTotal / 8, $dexTotal / 8],
                [$dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8, $dexTotal / 8],
                [$dexTotal / 8, $dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8],
            ]);
        } else if ($pickType == '지') {
            $leadership = $subStat;
            $strength = $otherStat;
            $intel = $mainStat;
            $dexVal = [$dexTotal / 8, $dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8];
        } else {
            $leadership = $otherStat;
            $strength = $subStat;
            $intel = $mainStat;
            $dexVal = [$dexTotal / 4, $dexTotal / 4, $dexTotal / 4, $dexTotal / 4];
        }

        $leadership = Util::round($leadership);
        $strength = Util::round($strength);
        $intel = Util::round($intel);

        $scoutType = "발견";
        $scoutLevel = 0;
        $scoutNation = 0;

        $age = Util::randRangeInt(20, 25);
        $birthYear = $env['year'] - $age;
        $deathYear = $env['year'] + Util::randRangeInt(10, 50);

        $cityID = Util::choiceRandom(array_keys(\sammo\CityConst::all()));
        $newNPC = new \sammo\Scenario\NPC(
            Util::randRangeInt(1, 150),
            \sammo\getRandGenName(),
            null,
            $scoutNation,
            $cityID,
            $leadership,
            $strength,
            $intel,
            $scoutLevel,
            $birthYear,
            $deathYear,
            null,
            null
        );
        $newNPC->npc = 3;
        $newNPC->setMoney(1000, 1000);
        $newNPC->setExpDed($avgGen['exp'], $avgGen['ded']);
        $newNPC->setSpecYear(
            Util::round((GameConst::$retirementYear - $age) / 12) + $age,
            Util::round((GameConst::$retirementYear - $age) / 6) + $age
        );
        $newNPC->setDex(
            $dexVal[0],
            $dexVal[1],
            $dexVal[2],
            $dexVal[3],
            $avgGen['dex5']
        );

        $newNPC->build($this->env);
        $npcName = $newNPC->realName;
        $josaRa = JosaUtil::pick($npcName, '라');

        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $logger->pushGeneralActionLog("<Y>$npcName</>{$josaRa}는 <C>인재</>를 {$scoutType}하였습니다! <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <Y>$npcName</>{$josaRa}는 <C>인재</>를 {$scoutType}하였습니다!");
        $logger->pushGeneralHistoryLog("<Y>$npcName</>{$josaRa}는 <C>인재</>를 {$scoutType}");

        $incStat = Util::choiceRandomUsingWeight([
            'leadership_exp' => $general->getLeadership(false, false, false, false),
            'strength_exp' => $general->getStrength(false, false, false, false),
            'intel_exp' => $general->getIntel(false, false, false, false)
        ]);
        [$reqGold, $reqRice] = $this->getCost();

        $exp = 200;
        $ded = 300;

        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar($incStat, 3);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);
        return true;
    }
}
