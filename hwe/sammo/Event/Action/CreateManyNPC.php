<?php
namespace sammo\Event\Action;
use \sammo\GameConst;
use \sammo\Util;
//기존 event_3.php
class CreateManyNPC extends \sammo\Event\Action{
    protected $npcCount;
    protected $avgGen;
    public function __construct($npcCount = 200){
        $this->npcCount = $npcCount;
    }

    protected function generateNPC($env){
        $pickTypeList = ['무'=>6, '지'=>6, '무지'=>3];

        $pickType = Util::choiceRandomUsingWeightPair($pickTypeList);

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

        if($pickType == '무'){
            $leadership = $subStat;
            $strength = $mainStat;
            $intel = $otherStat;
        }
        else if($pickType == '지'){
            $leadership = $subStat;
            $strength = $otherStat;
            $intel = $mainStat;
        }
        else{
            $leadership = $otherStat;
            $strength = $subStat;
            $intel = $mainStat;
        }

        $leadership = Util::round($leadership);
        $strength = Util::round($strength);
        $intel = Util::round($intel);

        $age = $env['year'] - 20;
        $cityID = Util::choiceRandom(array_keys(\sammo\CityConst::all()));
        $newNPC = new \sammo\Scenario\NPC(
            Util::randRangeInt(1, 150),
            \sammo\getRandGenName(),
            null,
            0,
            $cityID,
            $leadership,
            $strength,
            $intel,
            0,
            $age,
            $env['year'] + Util::randRangeInt(10, 50),
            null,
            null
        );
        $newNPC->npc = 6;
        $newNPC->setMoney(100, 100);
        $newNPC->setSpecYear(
            Util::round((GameConst::$retirementYear - $age)/12) + $age,
            Util::round((GameConst::$retirementYear - $age)/3) + $age
        );

        $newNPC->build($env);
        return [$newNPC->realName, $newNPC->generalID];
    }
    

    public function run($env=null){
        if($this->npcCount <= 0){
            return [__CLASS__, []];   
        }
        $result = [];
        foreach(Util::range($this->npcCount) as $idx){
            $result[] = $this->generateNPC($env);
        }

        $logger = new \sammo\ActionLogger(0, 0, $env['year'], $env['month']);
        $genCnt = count($result);
        if($genCnt == 1){
            $npcName = $result[0][0];
            $josaRa = \sammo\JosaUtil::pick($npcName, '라');
            $logger->pushGlobalActionLog("운영자가 <Y>$npcName</>{$josaRa}는 장수를 <S>생성</>하였습니다.");
        }
        else{
            $logger->pushGlobalActionLog("운영자가 장수 <C>{$genCnt}</>명을 <S>생성</>하였습니다.");
        }
        $logger->pushGlobalHistoryLog("운영자가 장수 <C>{$genCnt}</>명을 <S>생성</>했습니다.", \sammo\ActionLogger::NOTICE_YEAR_MONTH);
        $logger->flush();

        return [__CLASS__, $result];   
    }
}