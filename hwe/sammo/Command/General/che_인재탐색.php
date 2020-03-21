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

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    tryUniqueItemLottery,
    getAllNationStaticInfo
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_인재탐색 extends Command\GeneralCommand{
    static protected $actionName = '인재탐색';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setNation(['gennum', 'scout']);
        $env = $this->env;
        
        [$reqGold, $reqRice] = $this->getCost();

        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),  
        ];

        $relYear = $env['year'] - $env['startyear'];
        if($this->nation['nation'] != 0 && $relYear < 3 && $this->nation['gennum'] >= GameConst::$initialNationGenLimit){
            $nationName = $this->nation['name'];
            $josaUn = JosaUtil::pick($nationName, '은');
            $this->runnableConstraints[] = ConstraintHelper::AlwaysFail("현재 <D>{$nationName}</>{$josaUn} 탐색이 제한되고 있습니다.");
        }

    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}(랜덤경험";
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
        return [$this->env['develcost'], 0];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nationID = $general->getNationID();

        $maxGenCnt = $env['maxgeneral'];
        $nationCnt = count(getAllNationStaticInfo());

        $totalGenCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE npc <= 2');
        $totalNpcCnt = $db->queryFirstField('SELECT count(`no`) FROM general WHERE 3 <= npc AND npc <= 4');

        $genCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc < 2', $nationID);
        $npcCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND 3 <= npc AND npc <= 4', $nationID);

        $currCnt  = Util::toInt($totalGenCnt + $totalNpcCnt / 2);
        $remainSlot = $maxGenCnt - $currCnt;

        $avgCnt = $currCnt / $nationCnt;

        $foundPropMain = pow($remainSlot / $maxGenCnt, 6);
        $foundPropSmall = 1 / ($totalNpcCnt / 3 + 1);
        $foundPropBig = 1 / $maxGenCnt;

        if($totalNpcCnt < 50){
            $foundProp = max($foundPropMain, $foundPropSmall);
        }
        else{
            $foundProp = max($foundPropMain, $foundPropBig);
        }
        $foundNpc = Util::randBool($foundProp);

        $logger = $general->getLogger();

        if(!$foundNpc){
            $logger->pushGeneralActionLog("인재를 찾을 수 없었습니다. <1>$date</>");

            $incStat = Util::choiceRandomUsingWeight([
                'leadership2'=>$general->getLeadership(false, false, false, false),
                'strength2'=>$general->getStrength(false, false, false, false),
                'intel2'=>$general->getIntel(false, false, false, false)
            ]);
            [$reqGold, $reqRice] = $this->getCost();
    
            $exp = 100;
            $ded = 70;

            $exp = $general->onCalcStat($general, 'experience', $exp);
            $ded = $general->onCalcStat($general, 'dedication', $ded);

            $general->increaseVarWithLimit('gold', -$reqGold, 0);
            $general->increaseVarWithLimit('rice', -$reqRice, 0);
            $general->increaseVar('experience', $exp);
            $general->increaseVar('dedication', $ded);
            $general->increaseVar($incStat, 1);
            $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
            $general->checkStatChange();
            tryUniqueItemLottery($general);
            $general->applyDB($db);
            return true;
        }
        //인간적으로 너무 길어서 끊었다!

        $exp = 200;
        $ded = 300;

        $pickTypeList = ['무'=>6, '지'=>6, '무지'=>3];

        $pickType = Util::choiceRandomUsingWeightPair($pickTypeList);

        $mainStat = GameConst::$defaultStatMax - Util::randRangeInt(0, 10);
        $otherStat = GameConst::$defaultStatMin + Util::randRangeInt(0, 5);
        $subStat = GameConst::$defaultStatTotal - $mainStat - $otherStat;
        if($subStat < GameConst::$defaultStatMin){
            $subStat = $otherStat;
            $otherStat = GameConst::$defaultStatMin;
            $mainStat = GameConst::$defaultStatTotal - $subStat - $otherStat;
            if($mainStat){
                throw new \LogicException('기본 스탯 설정값이 잘못되어 있음');
            }
        }

        $avgGen = $db->queryFirstRow(
            'SELECT max(leadership+strength+intel) as stat_sum, avg(dedication) as ded,avg(experience) as exp,
            avg(dex0+dex10+dex20+dex30) as dex_t, avg(age) as age, avg(dex40) as dex40
            from general where nation=%i',
            $nationID
        );
        $dexTotal = $avgGen['dex_t'];

        if($pickType == '무'){
            $leadership = $subStat;
            $strength = $mainStat;
            $intel = $otherStat;
            $dexVal = Util::choiceRandom([
                [$dexTotal*5/8, $dexTotal/8, $dexTotal/8, $dexTotal/8],
                [$dexTotal/8, $dexTotal*5/8, $dexTotal/8, $dexTotal/8],
                [$dexTotal/8, $dexTotal/8, $dexTotal*5/8, $dexTotal/8],
            ]);
        }
        else if($pickType == '지'){
            $leadership = $subStat;
            $strength = $otherStat;
            $intel = $mainStat;
            $dexVal = [$dexTotal/8, $dexTotal/8, $dexTotal*5/8, $dexTotal/8];
        }
        else{
            $leadership = $otherStat;
            $strength = $subStat;
            $intel = $mainStat;
            $dexVal = [$dexTotal/4, $dexTotal/4, $dexTotal/4, $dexTotal/4];
        }

        // 국내 최고능치 기준으로 랜덤성 스케일링
        $maxLPI = $avgGen['stat_sum'];
        if($maxLPI > 210) {
            $leadership *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.6, 0.9);
            $strength *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.6, 0.9);
            $intel *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.6, 0.9);
        } elseif($maxLPI > 180) {
            $leadership *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.75, 0.95);
            $strength *=  $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.75, 0.95);
            $intel *= $avgGen['stat_sum'] / GameConst::$defaultStatTotal * Util::randRange(0.75, 0.95);
        } else {
            $leadership *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.9, 1);
            $strength *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.9, 1);
            $intel *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.9, 1);
        }
        $leadership = Util::round($leadership);
        $strength = Util::round($strength);
        $intel = Util::round($intel);


        $joinProp = 0.55 * $avgCnt / ($genCnt + $npcCnt / 2);
        $noScout = false;
        if($this->nation['scout'] != 0){
            $noScout = true;
        }
        else if($relYear < 3 && $this->nation['gennum'] >= GameConst::$initialNationGenLimit){
            $noScout = true;
        }

        if($noScout || !Util::randBool($joinProp)) {
            $scoutType = "발견";
            $scoutLevel = 0;
            $scoutNation = 0;
        } else {
            $scoutType = "영입";
            $scoutLevel = 1;
            $scoutNation = $nationID;
            $db->update('nation', [
                'gennum'=>$db->sqleval('gennum + 1')
            ], 'nation=%i', $nationID);
        }

        $age = $env['year'] - 20;

        $newNPC = new \sammo\Scenario\NPC(
            Util::randRangeInt(1, 150),
            \sammo\getRandGenName(),
            null,
            $scoutNation,
            $general->getCityID(),
            $leadership,
            $strength,
            $intel,
            $scoutLevel,
            $age,
            $env['year'] + Util::randRangeInt(10, 50),
            null,
            null
        );
        $newNPC->npc = 3;
        $newNPC->setMoney(100, 100);
        $newNPC->setExpDed($avgGen['exp'], $avgGen['ded']);
        $newNPC->setSpecYear(
            Util::round((GameConst::$retirementYear - $age)/12) + $age,
            Util::round((GameConst::$retirementYear - $age)/3) + $age
        );
        $newNPC->setDex(
            $dexVal[0],
            $dexVal[1],
            $dexVal[2],
            $dexVal[3],
            $avgGen['dex40']
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
            'leadership2'=>$general->getLeadership(false, false, false, false),
            'strength2'=>$general->getStrength(false, false, false, false),
            'intel2'=>$general->getIntel(false, false, false, false)
        ]);
        [$reqGold, $reqRice] = $this->getCost();

        $exp = 200;
        $ded = 300;

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar($incStat, 3);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);
        return true;
    }

    
}