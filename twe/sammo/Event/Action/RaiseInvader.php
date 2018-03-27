<?php
namespace sammo\Event\Action;
use sammo\Util;
use sammo\DB;

/**
 * 이민족 침입을 모사
 * 
 * 양수 : 정해진 값. [절대값]
 * 음수 : 합산(장수 등), 혹은 평균(기술 등)을 나누어 적용한 값 [상대값]
 * 
 * event_1.php, 센 이민족 : npcEachCount = -3.5, specAvg = 195, specDist = 5, tech = 15000, dex = 450000
 * event_2.php, 약한 이민족 : npcEachCount = -3.5, specAvg = 150, specDist = 20, tech = -1, dex = 0
 * event_3.php, 엄청 약한 이민족 : npcEachCount = 100, specAvg = 50, specDist = 5, tech = 0, dex = 0
 */
class RaiseInvader extends sammo\Event\Action{
    private $npcEachCount;
    private $specAvg;
    private $specDist;
    private $tech;
    private $dex;

    public function __construct($npcEachCount = -3.5, int $specAvg = 150, int $specDist = 20, int $tech = -1, int $dex = 0){
        $this->npcEachCount = $npcEachCount;
        $this->specAvg = $specAvg;
        $this->specDist = $specDist;
        $this->tech = $tech;
        $this->dex = $dex;
    }

    private function calcNpcEachCount(){

    }

    public function run($env=null){
        $db = DB::db();
        $npcEachCount = $this->npcEachCount;

        if($npcEachCount < 0){
            $npcEachCount = $db->queryFirstField('SELECT count(no) from general where npc<5');
            $npcEachCount /= -1 * $this->npcEachCount;
        }
        return [__CLASS__, 'NYI'];   
    }
}