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
    tryUniqueItemLottery
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

        $this->setNation(['gennum']);
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

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $nationID = $general->getNationID();

        $genCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc < 2', $nationID);
        $npcCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc == 3', $nationID);
        $npcOtherCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation!=%i AND npc == 32', $nationID);


        $genCount = Util::valueFit($genCount, 1);
        $npcCount = Util::valueFit($npcCount, 1);
        $npcOtherCountScore = Util::round(sqrt($npcOtherCount + 1)) - 1;

        //TODO: 수식 재 설계
        $randPick = 1 / (sqrt($genCount * $npcCount * $npcCount) + $npcOtherCount);

        $logger = $general->getLogger();

        if(!Util::randBool($randPick)){
            $logger->pushGeneralActionLog("인재를 찾을 수 없었습니다. <1>$date</>");

            $incStat = Util::choiceRandomUsingWeight([
                'leader2'=>$general->getLeadership(false, false, false, false),
                'power2'=>$general->getPower(false, false, false, false),
                'intel2'=>$general->getIntel(false, false, false, false)
            ]);
            [$reqGold, $reqRice] = $this->getCost();
    
            $exp = 100;
            $ded = 70;

            $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
            $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

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

        if($env['scenario'] < 100){
            $pickTypeList = ['무'=>4, '지'=>6];
        }
        else{
            $pickTypeList = ['무'=>4, '지'=>4, '무지'=>2];
        }

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
            'SELECT max(leader+power+intel) as lpi, avg(dedication) as ded,avg(experience) as exp,
            avg(dex0) as dex0, avg(dex10) as dex10, avg(dex20) as dex20, avg(dex30) as dex30, avg(dex40) as dex40
            from general where nation=%i',
            $nationID
        );

        if($pickType == '무'){
            $leader = $subStat;
            $power = $mainStat;
            $intel = $otherStat;
        }
        else if($pickType == '지'){
            $leader = $subStat;
            $power = $otherStat;
            $intel = $mainStat;
        }
        else{
            $leader = $otherStat;
            $power = $subStat;
            $intel = $mainStat;
        }

        // 국내 최고능치 기준으로 랜덤성 스케일링
        $maxLPI = $avgGen['lpi'];
        if($maxLPI > 210) {
            $leader *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.6, 0.9);
            $power *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.6, 0.9);
            $intel *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.6, 0.9);
        } elseif($maxLPI > 180) {
            $leader *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.75, 0.95);
            $power *=  $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.75, 0.95);
            $intel *= $avgGen['lpi'] / GameConst::$defaultStatTotal * Util::randRange(0.75, 0.95);
        } else {
            $leader *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.9, 1);
            $power *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.9, 1);
            $intel *= $maxLPI / GameConst::$defaultStatTotal * Util::randRange(0.9, 1);
        }
        $leader = Util::round($leader);
        $power = Util::round($power);
        $intel = Util::round($intel);

        $isOurNation = $this->nation['scout'] == 0;

        $newNPC = new \sammo\Scenario\NPC(
            Util::randRangeInt(1, 150),
            \sammo\getRandGenName(),
            null,
            $isOurNation?$nationID:0,
            $general->getCityID(),
            $leader,
            $power,
            $intel,
            $isOurNation?1:0,
            $env['year'] - 20,
            $env['year'] + Util::randRangeInt(10, 50),
            null,
            null
        );
        $newNPC->npc = 3;
        $newNPC->setMoney(100, 100);
        $newNPC->setExpDed($avgGen['exp'], $avgGen['ded']);
        $newNPC->setSpecYear(
            Util::round((80 - $age)/12) + $age,
            Util::round((80 - $age)/3) + $age
        );
        $newNPC->setDex(
            $avgGen['dex0'],
            $avgGen['dex10'],
            $avgGen['dex20'],
            $avgGen['dex30'],
            $avgGen['dex40']
        );

        $newNPC->build($this->env);
        $npcName = $newNPC->realName;
        $josaRa = JosaUtil::pick($npcName, '라');

        if($isOurNation){
            $scoutType = '영입';
            $db->update('nation', [
                'gennum'=>$db->sqleval('gennum + 1')
            ], 'nation=%i', $nationID);
        }
        else{
            $scoutType = '발견';
        }

        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $logger->pushGeneralActionLog("<Y>$npcName</>{$josaRa}는 <C>인재</>를 {$scoutType}하였습니다! <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <Y>$npcName</>{$josaRa}는 <C>인재</>를 {$scoutType}하였습니다!");
        $logger->pushGeneralHistoryLog("<Y>$npcName</>{$josaRa}는 <C>인재</>를 {$scoutType}");

        $incStat = Util::choiceRandomUsingWeight([
            'leader2'=>$general->getLeadership(false, false, false, false),
            'power2'=>$general->getPower(false, false, false, false),
            'intel2'=>$general->getIntel(false, false, false, false)
        ]);
        [$reqGold, $reqRice] = $this->getCost();

        $exp = 200;
        $ded = 300;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

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

        return true;
    }

    
}