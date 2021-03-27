<?php
namespace sammo\Event\Action;
use \sammo\GameConst;
use \sammo\Util;
use \sammo\DB;

use function sammo\pickGeneralFromPool;

//기존 event_3.php
class CreateManyNPC extends \sammo\Event\Action{
    protected $npcCount;
    protected $avgGen;
    public function __construct($npcCount = 10){
        $this->npcCount = $npcCount;
    }

    protected function generateNPC($env, int $cnt){
        $pickTypeList = ['무'=>1, '지'=>1];

        $age = Util::randRangeInt(20, 25);
        $birthYear = $env['year'] - $age;
        $deathYear = $env['year'] + Util::randRangeInt(10, 50);

        $result = [];
        foreach(pickGeneralFromPool(DB::db(), 0, $cnt) as $pickedNPC){
            $newNPC = $pickedNPC->getGeneralBuilder();
            $newNPC->setNationID(0)
            ->setNPCType(3)
            ->setMoney(1000, 1000)
            ->setExpDed(0, 0)
            ->setLifeSpan($birthYear, $deathYear);
            if($newNPC->getStat()[0]===null){
                $newNPC->fillRandomStat($pickTypeList);
            }
            $newNPC->fillRemainSpecAsZero($env);
            $newNPC->build($env);
            $pickedNPC->occupyGeneralName();
            $result[] = [
                $newNPC->getGeneralName(), $newNPC->getGeneralID()
            ];
        }
        return $result;
    }
    

    public function run(array $env){
        if($this->npcCount <= 0){
            return [__CLASS__, []];   
        }
        $result = $this->generateNPC($env, $this->npcCount);

        $logger = new \sammo\ActionLogger(0, 0, $env['year'], $env['month']);
        $genCnt = count($result);
        if($genCnt == 1){
            $npcName = $result[0][0];
            $josaRa = \sammo\JosaUtil::pick($npcName, '라');
            $logger->pushGlobalActionLog("<Y>$npcName</>{$josaRa}는 장수가 <S>등장</>하였습니다.");
        }
        else{
            $logger->pushGlobalActionLog("장수 <C>{$genCnt}</>명이 <S>등장</>하였습니다.");
        }
        $logger->pushGlobalHistoryLog("장수 <C>{$genCnt}</>명이 <S>등장</>했습니다.", \sammo\ActionLogger::NOTICE_YEAR_MONTH);
        $logger->flush();

        return [__CLASS__, $result];   
    }
}