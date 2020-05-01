<?php
namespace sammo\Event\Action;
use \sammo\GameConst;
use \sammo\Util;
//기존 event_3.php
class CreateManyNPC extends \sammo\Event\Action{
    protected $npcCount;
    protected $avgGen;
    public function __construct($npcCount = 200){
        \sammo\LogText('ctc',$npcCount);
        $this->npcCount = $npcCount;
    }

    protected function generateNPC($env){
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

        // 국내 최고능치 기준으로 랜덤성 스케일링
        $maxLPI =  GameConst::$defaultStatTotal;
        if($maxLPI > 210) {
            $leadership *= Util::randRange(0.6, 0.9);
            $strength *= Util::randRange(0.6, 0.9);
            $intel *= Util::randRange(0.6, 0.9);
        } elseif($maxLPI > 180) {
            $leadership *= Util::randRange(0.75, 0.95);
            $strength *=  Util::randRange(0.75, 0.95);
            $intel *= Util::randRange(0.75, 0.95);
        } else {
            $leadership *= Util::randRange(0.9, 1);
            $strength *= Util::randRange(0.9, 1);
            $intel *= Util::randRange(0.9, 1);
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