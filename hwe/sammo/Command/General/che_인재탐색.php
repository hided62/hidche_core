<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;

use function \sammo\tryUniqueItemLottery;
use function \sammo\pickGeneralFromPool;

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

        $totalGenCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE npc <= 2');
        $totalNpcCnt = $db->queryFirstField('SELECT count(`no`) FROM general WHERE 3 <= npc AND npc <= 4');

        $foundProp = $this->calcFoundProp($env['maxgeneral'], $totalGenCnt, $totalNpcCnt);
        $foundNpc = Util::randBool($foundProp);

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

        $exp = 100 * (sqrt(1 / $foundProp) + 1);
        $ded = 150 * (sqrt(1 / $foundProp) + 1);

        $scoutType = "발견";

        $age = Util::randRangeInt(20, 25);
        $birthYear = $env['year'] - $age;
        $deathYear = $env['year'] + Util::randRangeInt(10, 50);

        $avgGen = $db->queryFirstRow(
            'SELECT avg(dedication) as ded,avg(experience) as exp,
            avg(dex1+dex2+dex3+dex4) as dex_t, avg(age) as age, avg(dex5) as dex5
            from general where npc < 4'
        );

        $pickTypeList = ['무' => 6, '지' => 6, '무지' => 3];

        $pickedNPC = pickGeneralFromPool($db, 0, 1)[0];
        $newNPC = $pickedNPC->getGeneralBuilder();

        $newNPC->setSpecial('None', 'None');
        $newNPC->setNPCType(3);
        $newNPC->setMoney(1000, 1000);
        $newNPC->setLifeSpan($birthYear, $deathYear);
        $newNPC->setSpecYear(
            Util::round((GameConst::$retirementYear - $age) / 12) + $age,
            Util::round((GameConst::$retirementYear - $age) / 6) + $age
        );
        $newNPC->fillRemainSpecAsRandom($pickTypeList, $avgGen, $env);

        $newNPC->build($this->env);
        $pickedNPC->occupyGeneralName();
        $npcName = $newNPC->getGeneralName();
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

        $general->increaseInheritancePoint('active_action', Util::valueFit(sqrt(1 / $foundProp), 1));
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
