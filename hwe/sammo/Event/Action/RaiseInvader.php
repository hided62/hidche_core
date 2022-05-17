<?php

namespace sammo\Event\Action;

use Ds\Set;
use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\Json;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\Scenario\GeneralBuilder;
use sammo\Scenario\Nation;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\refreshNationStaticInfo;

/**
 * 이민족 침입을 모사
 *
 * 양수 : 정해진 값. [절대값]
 * 음수 : 합산(장수 등), 혹은 평균(기술 등)을 곱해 적용한 값 [상대값]
 *
 * event_1.php, 센 이민족 : npcEachCount = -2, specAvg = 195, tech = 15000, dex = 450000
 * event_2.php, 약한 이민족 : npcEachCount = -2, specAvg = 150, tech = -1, dex = 0
 * event_3.php, 엄청 약한 이민족 : npcEachCount = 100, specAvg = 50, tech = 0, dex = 0
 */
class RaiseInvader extends \sammo\Event\Action
{
    private $npcEachCount;
    private $specAvg;
    private $tech;
    private $dex;

    public function __construct(
        $npcEachCount = -3,
        $specAvg = -1.2,
        $tech = -1.2,
        $dex = -1
    ) {
        $this->npcEachCount = $npcEachCount;
        $this->specAvg = $specAvg;
        $this->tech = $tech;
        $this->dex = $dex;
    }

    private function moveCapital(RandUtil $rng): Set
    {
        $cities = [];
        foreach (CityConst::all() as $cityObj) {
            if ($cityObj->level != 4) {
                continue;
            }
            $cities[] = $cityObj->id;
        }

        $disabledInvaderCity = new Set();
        if (count($cities) == 0) {
            return $disabledInvaderCity;
        }

        $db = DB::db();

        foreach ($db->queryAllLists('SELECT capital, nation from nation WHERE capital in %li', $cities) as $row) {
            [$oldCapital, $nation] = $row;
            $capitalCandidates = $db->queryFirstColumn('SELECT from city WHERE nation = %i AND city != %i', $nation);
            if (!$capitalCandidates) {
                $disabledInvaderCity->add($oldCapital);
                continue;
            }
            $newCapital = $rng->choice($capitalCandidates);
            $db->update('nation', ['capital' => $newCapital], 'nation=%i', $nation);
            $db->update('general', ['city' => $newCapital], 'nation=%i and city=%i', $nation, $oldCapital);
        }


        $db->update('general', [
            'officer_level' => 1,
            'officer_city' => 0
        ], 'officer_city in %li', $cities);

        $db->update('city', [
            'nation' => 0,
            'front' => 0,
            'supply' => 1,
        ], 'city in %li', $cities);

        return $disabledInvaderCity;
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
                $db->queryFirstField('SELECT count(no) from general where npc < 4') / count($cities);
            $npcEachCount *= -1 * $this->npcEachCount;
        }
        $npcEachCount = max(10, Util::toInt($npcEachCount));

        $specAvg = $this->specAvg;
        if ($specAvg < 0) {
            $specAvg = $db->queryFirstField('SELECT avg((`leadership` + `strength` + `intel`)) from general where npc < 4');
            $specAvg *= -1 * $this->specAvg;
        }
        $specAvg = Util::toInt($specAvg / 3);

        $tech = $this->tech;
        if ($tech < 0) {
            $tech = $db->queryFirstField("SELECT avg(tech) from nation where `level`>0");
            $tech *= -1 * $this->tech;
        }

        $dex = $this->dex;
        if ($dex < 0) {
            $dex = $db->queryFirstField("SELECT avg((dex1 + dex2 + dex3 + dex4 + dex5)/5) from general where npc < 4");
            $dex *= -1 * $this->dex;
        }
        $dex = Util::toInt($dex);

        $year = $env['year'];
        $month = $env['month'];
        $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'RaiseInvader',
            $year,
            $month,
        )));

        $disabledInvaderCity = $this->moveCapital($rng);
        $serverID = UniqueConst::$serverID;
        $existNations = $db->queryFirstColumn("SELECT nation FROM `nation`");
        $lastNationID = max(
            max($existNations),
            $db->queryFirstField("SELECT max(`nation`) FROM `ng_old_nations` WHERE server_id = %s", $serverID),
        );

        $exp = $db->queryFirstField("SELECT avg(experience) from general where npc < 6");


        $db->update('general', [
            'gold' => 999999,
            'rice' => 999999,
        ], true);

        $invaderNationIDList = [];
        refreshNationStaticInfo();
        foreach ($cities as $cityObj) {
            if ($cityObj->level != 4) {
                continue;
            }
            if($disabledInvaderCity->contains($cityObj->id)){
                continue;
            }

            $lastNationID += 1;
            $invaderNationID = $lastNationID;
            $invaderNationIDList[] = $invaderNationID;

            $invaderName = $cityObj->name;
            $nationName = "ⓞ{$invaderName}족";
            $nationObj = new Nation($rng, $invaderNationID, $nationName, '#800080', 9999999, 9999999, "중원의 부패를 물리쳐라! 이민족 침범!", $tech, "che_병가", 2, [$cityObj->name]);
            $nationObj->build($env);

            $ruler = (new GeneralBuilder($rng, "{$invaderName}대왕", false, null, $lastNationID))
                ->setEgo('che_패권')
                ->setSpecial('che_인덕', 'che_척사')
                ->setLifeSpan($year - 20, $year + 20)
                ->setCityID($cityObj->id)
                ->setNPCType(9)
                ->setStat(Util::toInt($specAvg * 1.8), Util::toInt($specAvg * 1.8), Util::toInt($specAvg * 1.2))
                ->setAffinity(999)
                ->setExpDed(Util::toInt($exp * 1.2), null)
                ->setGoldRice(99999, 99999);
            $ruler->build($env);

            $nationObj->addGeneral($ruler);

            foreach (Util::range(1, $npcEachCount) as $invaderGenIdx) {
                $gen = (new GeneralBuilder($rng, "{$invaderName}장수{$invaderGenIdx}", false, null, $invaderNationID))
                    ->setEgo('che_패권')
                    ->setSpecial('che_인덕', 'che_척사')
                    ->setLifeSpan($year - 20, $year + 20)
                    ->setCityID($cityObj->id)
                    ->setNPCType(9)
                    ->setAffinity(999)
                    ->setExpDed($exp, null)
                    ->setGoldRice(99999, 99999);

                $leadership = $rng->nextRangeInt(Util::toInt($specAvg * 1.2), Util::toInt($specAvg * 1.4));
                $mainStat = $rng->nextRangeInt(Util::toInt($specAvg * 1.2), Util::toInt($specAvg * 1.4));
                $subStat = $specAvg * 3 - $leadership - $mainStat;

                if ($rng->nextBit()) {
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
                $gen->build($env);
                $nationObj->addGeneral($gen);
            }

            $nationObj->postBuild($env);
            refreshNationStaticInfo();
            $db->insert('event', [
                'target' => 'month',
                'priority' => 1000,
                'condition' => Json::encode(true),
                'action' => Json::encode([["AutoDeleteInvader", $invaderNationID]]),
            ]);

        }
        $db->insert('event', [
            'target' => 'month',
            'priority' => 1000,
            'condition' => Json::encode(true),
            'action' => Json::encode([["InvaderEnding"]]),
        ]);

        $db->update('diplomacy', [
            'state' => 1,
            'term' => 24,
        ], '(me IN %li AND you IN %li) OR (me IN %li AND you IN %li)', $existNations, $invaderNationIDList, $invaderNationIDList, $existNations);

        $db->update('diplomacy', [
            'state' => 7,
            'term' => 480,
        ], '(me IN %li AND you IN %li)', $invaderNationIDList, $invaderNationIDList);

        $cityMaxPop = $specAvg * $npcEachCount * 100 * 4;
        $db->update('city', [
            'pop_max' => $cityMaxPop,
            'def_max' => 100000,
            'wall_max' => 10000,
        ], 'nation IN %li', $invaderNationIDList);

        $db->update('city', [
            'pop' => $db->sqleval('pop_max'),
            'agri' => $db->sqleval('agri_max'),
            'comm' => $db->sqleval('comm_max'),
            'secu' => $db->sqleval('secu_max'),
        ], true);

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->setValue('isunited', 1);
        refreshNationStaticInfo();

        $logger = new ActionLogger(0, 0, $year, $env['month']);
        $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>각지의 이민족들이 <M>궐기</>합니다!");
        $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>중원의 전 국가에 <M>선전포고</> 합니다!");
        $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>이민족의 기세는 그 누구도 막을 수 없을듯 합니다!");
        $logger->flush();

        $gameStor->setValue('block_change_scout', false);

        $db->update('plock', [
            'plock' => 0
        ], true);

        return [__CLASS__, count($invaderNationIDList)];
    }
}
