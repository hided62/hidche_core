<?php

namespace sammo\Command\Nation;

use\sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    KVStorage
};

use function\sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic,
    CriticalScoreEx,
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_의병모집 extends Command\NationCommand
{
    static protected $actionName = '의병모집';

    protected function argTest(): bool
    {
        $this->arg = null;
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setNation(['strategic_cmd_limit']);
        $this->setCity();
        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $this->fullConditionConstraints = [
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::AvailableStrategicCommand(),
            ConstraintHelper::NotOpeningPart($relYear),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        $reqTurn = $this->getPreReqTurn() + 1;
        $postReqTurn = $this->getPostReqTurn();

        return "{$name}/{$reqTurn}턴(전략$postReqTurn)";
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 2;
    }

    public function getPostReqTurn(): int
    {
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount * 10) * 10);

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        return $nextTerm;
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $year = $this->env['year'];
        $month = $this->env['month'];

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $commandName = $this->getName();
        $josaUl = JosaUtil::pick($commandName, '을');

        $genCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc < 2', $nationID);
        $npcCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc = 3', $nationID);
        $npcOtherCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation!=%i AND npc = 3', $nationID);


        $genCount = Util::valueFit($genCount, 1);
        $npcCount = Util::valueFit($npcCount, 1);
        $npcOtherCountScore = Util::round(sqrt($npcOtherCount + 1)) - 1;

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("{$commandName} 발동! <1>$date</>");

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <M>{$commandName}</>{$josaUl} 발동하였습니다.";

        $nationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach ($nationGeneralList as $nationGeneralID) {
            $nationGeneralLogger = new ActionLogger($nationGeneralID, $nationID, $year, $month);
            $nationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $nationGeneralLogger->flush();
        }

        $logger->pushGeneralHistoryLog("<M>{$commandName}</>{$josaUl} 발동");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <M>{$commandName}</>{$josaUl} 발동");

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $gameStor = KVStorage::getStorage($db, 'game_env'); //TODO: 차라리 env가 이거여야..?

        $avgGenCnt = $db->queryFirstField('SELECT avg(gennum) FROM nation WHERE level > 0');
        $createGenCnt = 5 + Util::round($avgGenCnt / 10);
        $createGenIdx = $gameStor->npccount + 1;
        $lastCreatGenIdx = $createGenIdx + $createGenCnt;

        $pickTypeList = ['무' => 5, '지' => 5];

        $avgGen = $db->queryFirstRow(
            'SELECT avg(dedication) as ded,avg(experience) as exp,
            avg(dex1+dex2+dex3+dex4) as dex_t, avg(age) as age, avg(dex5) as dex5
            from general where nation=%i',
            $nationID
        );
        $dexTotal = $avgGen['dex_t'];

        for (; $createGenIdx <= $lastCreatGenIdx; $createGenIdx++) {
            $pickType = Util::choiceRandomUsingWeight($pickTypeList);

            $totalStat = GameConst::$defaultStatNPCMax * 2 + 10;
            $minStat = 10;
            $mainStat = GameConst::$defaultStatNPCMax - Util::randRangeInt(0, 10);
            //TODO: defaultStatNPCTotal, defaultStatNPCMin 추가
            $otherStat = $minStat + Util::randRangeInt(0, 5);
            $subStat = $totalStat - $mainStat - $otherStat;
            if ($subStat < $minStat) {
                $subStat = $otherStat;
                $otherStat = $minStat;
                $mainStat = $totalStat - $subStat - $otherStat;
                if ($mainStat) {
                    throw new \LogicException('기본 스탯 설정값이 잘못되어 있음');
                }
            }

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

            $age = $avgGen['age'];

            $newNPC = new \sammo\Scenario\NPC(
                Util::randRangeInt(1, 150),
                "의병장{$createGenIdx}",
                null,
                $nationID,
                $general->getCityID(),
                $leadership,
                $strength,
                $intel,
                1,
                $env['year'] - 20,
                $env['year'] + 6,
                null,
                null
            );
            $newNPC->killturn = Util::randRangeInt(64, 70);
            $newNPC->npc = 4;
            $newNPC->setMoney(1000, 1000);
            $newNPC->setExpDed($avgGen['exp'], $avgGen['ded']);
            $newNPC->setDex(
                $dexVal[0],
                $dexVal[1],
                $dexVal[2],
                $dexVal[3],
                $avgGen['dex5']
            );

            $newNPC->build($this->env);
        }

        $gameStor->npccount = $lastCreatGenIdx;
        $db->update('nation', [
            'gennum' => $db->sqleval('gennum + %i', $createGenCnt),
            'strategic_cmd_limit' => $this->getPostReqTurn()
        ], 'nation=%i', $nationID);

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        return true;
    }
}
