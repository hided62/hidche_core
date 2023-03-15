<?php

namespace sammo\Event\Action;

use \sammo\GameConst;
use \sammo\Util;
use \sammo\DB;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;

use function sammo\pickGeneralFromPool;

//기존 event_3.php
class CreateManyNPC extends \sammo\Event\Action
{
    protected $npcCount;
    protected $fillCnt;
    protected $avgGen;
    public function __construct($npcCount = 10, $fillCnt = 0)
    {
        $this->npcCount = $npcCount;
        $this->fillCnt = $fillCnt;
    }

    protected function generateNPC($env, int $cnt)
    {
        $pickTypeList = ['무' => 0.333, '지' => 0.333, '무지' => 0.334];

        $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'CreateManyNPC',
            $env['year'],
            $env['month']
        )));

        $result = [];
        foreach (pickGeneralFromPool(DB::db(), $rng, 0, $cnt) as $pickedNPC) {
            $age = $rng->nextRangeInt(20, 25);
            $birthYear = $env['year'] - $age;
            $deathYear = $env['year'] + $rng->nextRangeInt(10, 50);
            $newNPC = $pickedNPC->getGeneralBuilder();
            $newNPC->setNationID(0)
                ->setNPCType(3)
                ->setMoney(1000, 1000)
                ->setExpDed(0, 0)
                ->setLifeSpan($birthYear, $deathYear);
            if ($newNPC->getStat()[0] === null) {
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


    public function run(array $env)
    {
        if ($this->npcCount <= 0 && $this->fillCnt <= 0) {
            return [__CLASS__, []];
        }


        $moreGenCnt = 0;
        if ($this->fillCnt) {
            $db = DB::db();
            $nations = $db->queryFirstColumn('SELECT nation FROM general WHERE npc < 3 AND officer_level = 12');
            $regGens = 0;
            if($nations){
                $regGens = $db->queryFirstField('SELECT count(*) FROM general WHERE nation IN %li AND npc < 4', $nations);
            }
            $moreGenCnt = count($nations) * $this->fillCnt - $regGens;
        }

        $result = $this->generateNPC($env, $this->npcCount + $moreGenCnt);

        $logger = new \sammo\ActionLogger(0, 0, $env['year'], $env['month']);
        $genCnt = count($result);
        if ($genCnt == 1) {
            $npcName = $result[0][0];
            $josaRa = \sammo\JosaUtil::pick($npcName, '라');
            $logger->pushGlobalActionLog("<Y>$npcName</>{$josaRa}는 장수가 <S>등장</>하였습니다.");
        } else {
            $logger->pushGlobalActionLog("장수 <C>{$genCnt}</>명이 <S>등장</>하였습니다.");
        }
        $logger->pushGlobalHistoryLog("장수 <C>{$genCnt}</>명이 <S>등장</>했습니다.", \sammo\ActionLogger::NOTICE_YEAR_MONTH);
        $logger->flush();

        return [__CLASS__, $result];
    }
}
