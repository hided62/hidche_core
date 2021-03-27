<?php

namespace sammo\Event\Action;

use sammo\CityConst;
use sammo\DB;
use sammo\Json;
use sammo\KVStorage;
use sammo\Scenario\GeneralBuilder;
use sammo\Scenario\Nation;
use sammo\UniqueConst;
use sammo\Util;

/**
 * 이민족 침입을 모사
 * 
 * 양수 : 정해진 값. [절대값]
 * 음수 : 합산(장수 등), 혹은 평균(기술 등)을 곱해 적용한 값 [상대값]
 * 
 * event_1.php, 센 이민족 : npcEachCount = -0.5, specAvg = 195, tech = 15000, dex = 450000
 * event_2.php, 약한 이민족 : npcEachCount = -0.5, specAvg = 150, tech = -1, dex = 0
 * event_3.php, 엄청 약한 이민족 : npcEachCount = 100, specAvg = 50, tech = 0, dex = 0
 */
class RaiseInvader extends \sammo\Event\Action
{
    private $npcEachCount;
    private $specAvg;
    private $tech;
    private $dex;

    public function __construct(
        $npcEachCount = -0.5,
        int $specAvg = 50,
        int $tech = -1,
        $dex = -0.01
    ) {
        $this->npcEachCount = $npcEachCount;
        $this->specAvg = $specAvg;
        $this->tech = $tech;
        $this->dex = $dex;
    }

    private function moveCapital()
    {
        $cities = [];
        foreach (CityConst::all() as $cityObj) {
            if ($cityObj->level != 4) {
                continue;
            }
            $cities[] = $cityObj->id;
        }

        if (count($cities) == 0) {
            return [__CLASS__, 0];
        }

        $db = DB::db();


        foreach ($db->queryFirstColumn('SELECT capital, nation from nation WHERE capital in %li', $cities) as $row) {
            list($oldCapital, $nation) = $row;
            $newCapital = $db->queryFirstRow('SELECT city from city where nation=%i and city !=%i \
                order by rand() limit 1', $nation, $oldCapital);
            $db->update('nation', ['capital' => $newCapital], 'nation=%i', $nation);

            $db->update('general', ['city' => $newCapital], 'nation=%i and city=%i', $nation, $oldCapital);
        }


        $db->update('general', [
            'officer_level' => 1,
            'officer_city' => 0
        ], 'officer_city in %li', $cities);

        $db->update('city', [
            'nation' => 0,
            'front'=>0,
            'supply'=>1,
        ], 'city in %li', $cities);
    }

    public function run(array $env)
    {
        $db = DB::db();
        $npcEachCount = $this->npcEachCount;

        /** @var \sammo\CityInitDetail[] */
        $cities = [];
        foreach (CityConst::all() as $cityObj) {
            if ($cityObj->level != 4) {
                continue;
            }
            $cities[] = $cityObj;
        }

        if ($npcEachCount < 0) {
            $npcEachCount =
                $db->queryFirstField('SELECT count(no) from general where npc < 5') / count($cities);
            $npcEachCount *= -1 * $this->npcEachCount;
        }
        $npcEachCount = max(10, Util::toInt($npcEachCount));

        $specAvg = $this->specAvg;
        if ($specAvg < 0) {
            $specAvg = $db->queryFirstField('SELECT avg(sum(`leadership` + `strength` + `intel`)) from general where npc < 5');
            $specAvg *= -1 * $this->specAvg;
        }
        $specAvg = Util::toInt($specAvg);

        $tech = $this->tech;
        if ($tech < 0) {
            $tech = $db->queryFirstField("SELECT avg(tech) from nation where `level`>0");
            $tech *= -1 * $this->tech;
        }

        $dex = $this->dex;
        if ($dex < 0) {
            $dex = $db->queryFirstField("SELECT avg((dex1 + dex2 + dex3 + dex4 + dex5)/5) from general where npc < 5");
            $dex *= -1 * $this->dex;
        }
        $dex = Util::toInt($dex);

        $this->moveCapital();
        $serverID = UniqueConst::$serverID;
        $existNations = $db->queryFirstColumn("SELECT nation FROM `nation`");
        $lastNationID = max(
            max($existNations),
            $db->queryFirstField("SELECT max(`nation`) FROM `ng_old_nations` WHERE server_id = %s", $serverID),
        );


        $db->update('general', [
            'gold' => 999999,
            'rice' => 999999,
        ], true);

        $year = $env['year'];

        $invaderNationIDList = [];

        foreach ($cities as $cityObj) {
            if ($cityObj->level != 4) {
                continue;
            }

            $lastNationID += 1;
            $invaderNationID = $lastNationID;
            $invaderNationIDList[] = $invaderNationID;

            $invaderName = $cityObj->name;
            $nationName = "ⓞ{$invaderName}족";
            $cityID = $cityObj->id;
            $nationObj = new Nation($invaderNationID, $nationName, '#800080', 9999999, 9999999, "중원의 부패를 물리쳐라! 이민족 침범!", $tech, "che_병가", 2, [$cityID]);
            $nationObj->addGeneral((new GeneralBuilder("{$invaderName}대왕", false, null, $lastNationID))
                    ->setEgo('che_패권')
                    ->setSpecial('che_인덕', 'che_척사')
                    ->setLifeSpan($year - 20, $year + 20)
                    ->setNPCType(9)
                    ->setStat(Util::toInt($specAvg * 1.8), Util::toInt($specAvg * 1.8), Util::toInt($specAvg * 1.2))
                    ->setAffinity(999)
                    ->setGoldRice(99999, 99999)
            );

            foreach (Util::range(1, $npcEachCount) as $invaderGenIdx) {
                $gen = (new GeneralBuilder("{$invaderName}장수{$invaderGenIdx}", false, null, $invaderNationID))
                    ->setEgo('che_패권')
                    ->setSpecial('che_인덕', 'che_척사')
                    ->setLifeSpan($year - 20, $year + 20)
                    ->setNPCType(9)
                    ->setAffinity(999)
                    ->setGoldRice(99999, 99999);

                $leadership = Util::randRangeInt(Util::toInt($specAvg * 1.2), Util::toInt($specAvg * 1.4));
                $mainStat = Util::randRangeInt(Util::toInt($specAvg * 1.2), Util::toInt($specAvg * 1.4));
                $subStat = $specAvg * 3 - $leadership - $mainStat;

                if (Util::randBool()) {
                    //무장
                    $dexTable = [$dex * 2, $dex, $dex];
                    shuffle($dexTable);
                    $gen->setStat($leadership, $mainStat, $subStat)
                        ->setDex($dexTable[0], $dexTable[1], $dexTable[2], $dex, 0);
                } else {
                    //지장
                    $gen->setStat($leadership, $subStat, $mainStat)
                        ->setDex($dex, $dex, $dex, $dex * 2, 0);
                }
                $nationObj->addGeneral($gen);
            }

            $nationObj->build($env);
            $nationObj->postBuild($env);
            $db->insert('event', [
                'condition'=>Json::encode(true),
                'action'=>Json::encode(["AutoDeleteInvader", $invaderNationID]),
            ]);
        }

        $db->update('nation', [
            'scout' => 1
        ], 'nation IN %li', $invaderNationIDList);
        $db->update('diplomacy', [
            'state' => 1,
            'term' => 24,
        ], '(me IN %li AND you IN %li) OR (me IN %li AND you IN %li)', $existNations, $invaderNationIDList, $invaderNationIDList, $existNations);

        $cityMaxPop = $specAvg*$npcEachCount*100;
        $db->update('city', [
            'pop_max'=>$cityMaxPop,
            'def_max'=>10000,
            'wall_max'=>1000,
        ], 'nation IN %li', $invaderNationIDList);

        $db->update('city', [
            'pop'=>$db->sqleval('pop_max'),
            'secu'=>$db->sqleval('secu_max'),
            'def'=>$db->sqleval('def_max'),
            'wall'=>$db->sqleval('wall_max'),
        ], true);

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->isunited = 1;

        return [__CLASS__, count($invaderNationIDList)];
    }
}
