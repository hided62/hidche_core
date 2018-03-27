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
 * event_1.php, 센 이민족 : npcEachCount = -0.5, specAvg = 195, specDist = 5, tech = 15000, dex = 450000
 * event_2.php, 약한 이민족 : npcEachCount = -0.5, specAvg = 150, specDist = 20, tech = -1, dex = 0
 * event_3.php, 엄청 약한 이민족 : npcEachCount = 100, specAvg = 50, specDist = 5, tech = 0, dex = 0
 */
class RaiseInvader extends sammo\Event\Action{
    private $npcEachCount;
    private $specAvg;
    private $specDist;
    private $tech;
    private $dex;

    const INVADER_LIST = [
        '강'=>63,
        '저'=>64,
        '흉노'=>65,
        '남만'=>66,
        '산월'=>67,
        '오환'=>68,
        '왜'=>69
    ];

    public function __construct(
        $npcEachCount = -0.5,
        int $specAvg = 150,
        int $specDist = 20,
        int $tech = -1,
        int $dex = 0
    ){
        $this->npcEachCount = $npcEachCount;
        $this->specAvg = $specAvg;
        $this->specDist = $specDist;
        $this->tech = $tech;
        $this->dex = $dex;

        if($specDist < 0){
            throw new \InvalidArgumentException('specDist는 음수를 지원하지 않습니다.');
        }
    }

    private function moveCapital(){
        $cities = array_map(function ($value) {
            return $value;
        }, INVADER_LIST);

        $db = DB::db();


        foreach($db->queryFirstColumn('SELECT capital, nation from nation WHERE capital in (%li)', $cities) as $row){
            list($oldCapital, $nation) = $row;
            $newCapital = $db->queryFirstRow('SELECT city from city where nation=%i and city !=%i \
                order by rand() limit 1', $nation, $oldCapital);
            $db->update('nation', ['capital'=>$newCapital], 'nation=%i', $nation);

            $db->update('general', ['city'=>$newCapital], 'nation=%i and city=%i', $nation, $city);
        }

        $generals = [];
        foreach($db->query('SELECT gen1, gen2, gen3 from city where city in (%li)', $cities) as $city){
            list($gen1, $gen2, $gen3) = $city;
            if($gen1 != 0) $generals[]=$gen1;
            if($gen2 != 0) $generals[]=$gen2;
            if($gen3 != 0) $generals[]=$gen3;
        }

        $db->update('general', [
            'level'=>1
        ], 'no in (%li)', $generals);

        $db->update('city', [
            'gen1'=>0,
            'gen2'=>0,
            'gen3'=>0,
            'nation'=>0
        ], 'city in (%li)', $cities);
    }

    public function run($env=null){
        $db = DB::db();
        $npcEachCount = $this->npcEachCount;

        if($npcEachCount < 0){
            $npcEachCount = 
                $db->queryFirstField('SELECT count(no) from general where npc<5') / count(self::INVADER_LIST);
            $npcEachCount /= -1 * $this->npcEachCount;
        }

        $specAvg = $this->specAvg;
        if($specAvg < 0){
            $specAvg = $db->queryFirstField('SELECT avg(sum(`leader` + `power` + `intel`)) from general where npc<5');
            $specAvg /= -1 * $this->specAvg;
        }

        $tech = $this->tech;
        if($tech < 0){
            $tech = $db->queryFirstField("SELECT avg(tech) from nation where `level`>0");
            $tech /= -1 * $this->tech;
        }

        $dex = $this->dex;
        if($dex < 0){
            $dex = $db->queryFirstField("SELECT avg(dex0 + dex10 + dex20 + dex30 + dex40)/5 from nation where `level`>0");
            $dex /= -1 * $this->dex;
        }

        $this->moveCapital();
        //TODO:국가를 만들고
        //TODO:장수를 세팅하고
        //TODO:외교를 설정한다.

        //TODO: 시나리오 구현 후 마무리.

        return [__CLASS__, 'NYI'];   
    }
}