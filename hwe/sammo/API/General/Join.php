<?php

namespace sammo\API\General;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\GameUnitConst;
use sammo\General;
use sammo\InheritancePointManager;
use sammo\JosaUtil;
use sammo\Json;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\RootDB;
use sammo\Session;
use sammo\SpecialityHelper;
use sammo\StringUtil;
use sammo\TimeUtil;
use sammo\UniqueConst;
use sammo\UserLogger;
use sammo\Util;
use sammo\Validator;
use sammo\WebUtil;

use function sammo\addTurn;
use function sammo\cutTurn;
use function sammo\getGeneralSpecialWarName;
use function sammo\getRandTurn;
use function sammo\pushAdminLog;

class Join extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v
            ->rule('required', [
                'name',
                'leadership',
                'strength',
                'intel',
                'pic',
                'character',
            ])
            ->rule('int', [
                'leadership',
                'strength',
                'intel',
                'inheritTurntime',
            ])
            ->rule('boolean', [
                'pic'
            ])
            ->rule('stringWidthBetween', 'name', 1, 18)
            ->rule('min', [
                'leadership',
                'strength',
                'intel'
            ], GameConst::$defaultStatMin)
            ->rule('max', [
                'leadership',
                'strength',
                'intel'
            ], GameConst::$defaultStatMax)
            ->rule('in', 'character', array_merge(GameConst::$availablePersonality, ['Random']))
            ->rule('in', 'inheritSpecial', GameConst::$availableSpecialWar)
            ->rule('min', 'inheritTurntime', 0)
            ->rule('in', 'inheritCity', array_keys(CityConst::all()))
            ->rule('integerArray', 'inheritBonusStat');

        if (!$v->validate()) {
            return $v->errorStr();
        }

        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $userID = $session->userID;

        $name       = $this->args['name'];
        $name       = htmlspecialchars($name);
        $name       = StringUtil::removeSpecialCharacter($name);
        $name       = WebUtil::htmlPurify($name);
        $name       = StringUtil::textStrip($name);
        $pic        = $this->args['pic'];
        $character  = $this->args['character'];

        $leadership = $this->args['leadership'];
        $strength = $this->args['strength'];
        $intel = $this->args['intel'];

        $inheritSpecial = $this->args['inheritSpecial'] ?? null;
        $inheritTurntime = $this->args['inheritTurntime'] ?? null;
        $inheritCity = $this->args['inheritCity'] ?? null;
        $inheritBonusStat = $this->args['inheritBonusStat'] ?? null;

        if($inheritTurntime !== null && $inheritCity !== null){
            return '?????? ????????? ????????? ????????? ??? ????????????.';
        }

        $rootDB = RootDB::db();
        //?????? ??????????????? ????????????
        $member = $rootDB->queryFirstRow('SELECT `no`, id, picture, grade, `name`, imgsvr FROM member WHERE no=%i', $userID);

        if (!$member) {
            return "????????? ???????????????!!!";
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->cacheValues(['year', 'month', 'maxgeneral', 'scenario', 'show_img_level', 'turnterm', 'turntime', 'genius', 'npcmode']);
        ########## ?????? ?????? ???????????? ??????. ##########

        $gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');
        $oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);
        $oldName = $db->queryFirstField('SELECT `no` FROM general WHERE `name`=%s', $name);

        if ($oldGeneral) {
            return "?????? ?????????????????????!";
        }
        if ($oldName) {
            return "?????? ?????? ???????????????. ?????? ???????????? ????????? ?????????!";
        }
        if ($gameStor->maxgeneral <= $gencount) {
            return "????????? ????????? ??? ????????????!";
        }
        if ($name == '') {
            return "????????? ????????????. ?????? ??????????????????!";
        }
        if (mb_strwidth($name) > 18) {
            return "????????? ???????????? ????????????. ?????? ??????????????????!";
        }
        if ($leadership + $strength + $intel > GameConst::$defaultStatTotal) {
            return "???????????? " . GameConst::$defaultStatTotal . "??? ??????????????????. ?????? ??????????????????!";
        }

        if ($inheritBonusStat) {
            if (count($inheritBonusStat) != 3) {
                return "????????? ???????????? ?????? ?????????????????????. ?????? ??????????????????!";
            }
            foreach ($inheritBonusStat as $stat) {
                if ($stat < 0) {
                    return "????????? ???????????? ???????????????. ?????? ??????????????????!";
                }
            }
            $sum = array_sum($inheritBonusStat);
            if ($sum == 0) {
                $inheritBonusStat = null;
            } else if ($sum < 3 || $sum > 5) {
                return "????????? ????????? ?????? ?????? ?????????????????????. ?????? ??????????????????!";
            }
        }

        $admin = $gameStor->getValues(['scenario', 'turnterm', 'turntime', 'show_img_level', 'startyear', 'year', 'month']);

        $inheritPointManager = InheritancePointManager::getInstance();
        $inheritTotalPoint = $inheritPointManager->applyInheritanceUser($userID);
        $inheritRequiredPoint = 0;

        $userLogger = new UserLogger($userID, $admin['year'], $admin['month'], false);

        $now = TimeUtil::now(false);
        $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'MakeGeneral',
            $userID,
            $now
        )));

        if ($inheritCity !== null) {
            $inheritRequiredPoint += GameConst::$inheritBornCityPoint;
        }
        if ($inheritBonusStat !== null) {
            $inheritRequiredPoint += GameConst::$inheritBornStatPoint;
        }
        if ($inheritSpecial !== null) {
            $inheritRequiredPoint += GameConst::$inheritBornSpecialPoint;
        }
        if ($inheritTurntime !== null) {
            $inheritRequiredPoint += GameConst::$inheritBornTurntimePoint;
        }

        if ($inheritTotalPoint < $inheritRequiredPoint) {
            return "?????? ???????????? ???????????????. ?????? ??????????????????!";
        }

        if ($inheritSpecial !== null && $gameStor->genius == 0) {
            return "?????? ????????? ?????? ??????????????????. ?????? ??????????????????!";
        }

        if ($inheritCity !== null && !key_exists($inheritCity, CityConst::all())) {
            return "????????? ?????? ?????????????????????. ?????? ??????????????????!";
        }

        if ($inheritSpecial) {
            $speicalText = getGeneralSpecialWarName($inheritSpecial);
            $userLogger->push("{$speicalText} ?????? ????????? ?????? ?????? ??????", "inheritPoint");
            $genius = true;
        } else {
            // ?????? 1%
            $genius = $rng->nextBool(0.01);
        }

        if ($genius && $gameStor->genius > 0) {
            $gameStor->genius = $gameStor->genius - 1;
        } else {
            $genius = false;
        }

        if ($inheritCity !== null) {
            $cityname = CityConst::byID($inheritCity)->name;
            $userLogger->push("{$cityname}??? ?????? ??????", "inheritPoint");
            $city = $inheritCity;
        } else {
            // ?????????????????? ????????????
            $cities = $db->queryFirstColumn('SELECT city FROM city where `level`>=5 and `level`<=6 and nation=0');
            if (!$cities) {
                $cities = $db->queryFirstColumn('SELECT city FROM city where `level`>=5 and `level`<=6');
            }
            $city = $rng->choice($cities);
        }

        if ($inheritBonusStat) {
            [$pleadership, $pstrength, $pintel] = $inheritBonusStat;
            $userLogger->push("{$pleadership}, {$pstrength}, {$pintel} ????????? ???????????? ??????", "inheritPoint");
        } else {
            $pleadership = 0;
            $pstrength = 0;
            $pintel = 0;
            foreach (Util::range($rng->nextRangeInt(3, 5)) as $statIdx) {
                switch ($rng->choiceUsingWeight([$leadership, $strength, $intel])) {
                    case 0:
                        $pleadership++;
                        break;
                    case 1:
                        $pstrength++;
                        break;
                    case 2:
                        $pintel++;
                        break;
                }
            }
        }

        $leadership = $leadership + $pleadership;
        $strength = $strength + $pstrength;
        $intel = $intel + $pintel;

        $relYear = Util::valueFit($admin['year'] - $admin['startyear'], 0);

        $age = 20 + ($pleadership + $pstrength + $pintel) * 2 - $rng->nextRangeInt(0, 1);
        // ?????? ????????? ???????????????????????? ?????? ??????
        if ($genius) {
            $specage2 = $age;
            if ($inheritSpecial) {
                $special2 = $inheritSpecial;
            } else {
                $special2 = SpecialityHelper::pickSpecialWar($rng, [
                    'leadership' => $leadership,
                    'strength' => $strength,
                    'intel' => $intel,
                    'dex1' => 0,
                    'dex2' => 0,
                    'dex3' => 0,
                    'dex4' => 0,
                    'dex5' => 0
                ]);
            }
        } else {
            $specage2 = Util::valueFit(Util::round((GameConst::$retirementYear - $age) / 6 - $relYear / 2), 3) + $age;
            $special2 = GameConst::$defaultSpecialWar;
        }
        //??????
        $specage = Util::valueFit(Util::round((GameConst::$retirementYear - $age) / 12 - $relYear / 2), 3) + $age;
        $special = GameConst::$defaultSpecialDomestic;

        if ($admin['scenario'] >= 1000) {
            $specage2 = $age + 3;
            $specage = $age + 3;
        }

        if ($relYear < 3) {
            $experience = 0;
        } else {
            $expGenCount = $db->queryFirstField('SELECT count(*) FROM general WHERE nation != 0 AND npc < 4');
            $targetGenOrder = Util::round($expGenCount * 0.2);
            $experience = $db->queryFirstField(
                'SELECT experience FROM general WHERE nation != 0 AND npc < 4 ORDER BY experience ASC LIMIT %i, 1',
                $targetGenOrder - 1
            );
            $experience *= 0.8;
        }

        if ($inheritTurntime !== null) {
            $inheritTurntime = $inheritTurntime % ($admin['turnterm'] * 60);

            $userLogger->push(sprintf("??? ?????? %02d:%02d ??? ??????", intdiv($inheritTurntime, 60), $inheritTurntime%60), "inheritPoint");

            $inheritTurntime += $rng->nextRangeInt(0, 999999) / 1000000;
            $turntime = new \DateTimeImmutable(cutTurn($admin['turntime'], $admin['turnterm']));
            $turntime = $turntime->add(TimeUtil::secondsToDateInterval($inheritTurntime));
            $turntime = TimeUtil::format($turntime, true);
        } else {
            $turntime = getRandTurn($rng, $admin['turnterm'], new \DateTimeImmutable($admin['turntime']));
        }


        $now = TimeUtil::now(true);
        if ($now >= $turntime) {
            $turntime = addTurn($turntime, $admin['turnterm']);
        }

        //?????? ??????
        if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "" && $pic) {
            $face = $member['picture'];
            $imgsvr = $member['imgsvr'];
        } else {
            $face = "default.jpg";
            $imgsvr = 0;
        }

        //?????? ?????????
        if (!in_array($character, GameConst::$availablePersonality)) {
            $character = $rng->choice(GameConst::$availablePersonality);
        }
        //?????? ??????
        $affinity = $rng->nextRangeInt(1, 150);

        ########## ???????????? ???????????? ???????????? ????????????. ##########
        $db->insert('general', [
            'owner' => $userID,
            'name' => $name,
            'owner_name' => $member['name'],
            'picture' => $face,
            'imgsvr' => $imgsvr,
            'nation' => 0,
            'city' => $city,
            'troop' => 0,
            'affinity' => $affinity,
            'leadership' => $leadership,
            'strength' => $strength,
            'intel' => $intel,
            'experience' => $experience,
            'dedication' => 0,
            'gold' => GameConst::$defaultGold,
            'rice' => GameConst::$defaultRice,
            'crew' => 0,
            'train' => 0,
            'atmos' => 0,
            'officer_level' => 0,
            'turntime' => $turntime,
            'killturn' => 6,
            'lastconnect' => $now,
            'lastrefresh' => $now,
            'crewtype' => GameUnitConst::DEFAULT_CREWTYPE,
            'makelimit' => 0,
            'age' => $age,
            'startage' => $age,
            'personal' => $character,
            'specage' => $specage,
            'special' => $special,
            'specage2' => $specage2,
            'special2' => $special2
        ]);
        $generalID = $db->insertId();
        $turnRows = [];
        foreach (Util::range(GameConst::$maxTurn) as $turnIdx) {
            $turnRows[] = [
                'general_id' => $generalID,
                'turn_idx' => $turnIdx,
                'action' => '??????',
                'arg' => null,
                'brief' => '??????'
            ];
        }
        $db->insert('general_turn', $turnRows);

        $rank_data = [];
        foreach (RankColumn::cases() as $rankColumn) {
            $rank_data[] = [
                'general_id' => $generalID,
                'nation_id' => 0,
                'type' => $rankColumn->value,
                'value' => 0
            ];
        }
        $db->insert('rank_data', $rank_data);
        $cityname = CityConst::byID($city)->name;


        if ($inheritRequiredPoint > 0) {
            $userLogger->push("?????? ???????????? ????????? {$inheritRequiredPoint} ??????", "inheritPoint");
            $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$userID}");
            $inheritStor->setValue('previous', [$inheritTotalPoint - $inheritRequiredPoint, null]);
            $userLogger->flush();
            $db->update('rank_data', [
                'value'=>$db->sqleval('value + %i', $inheritRequiredPoint)
            ], 'general_id = %i AND type = %s', $generalID, RankColumn::inherit_point_spent_dynamic->value);
        }

        $me = [
            'no' => $generalID
        ];

        $log = [];
        $mylog = [];

        $logger = new ActionLogger($generalID, 0, $gameStor->year, $gameStor->month);

        $josaRa = JosaUtil::pick($name, '???');
        $speicalText = getGeneralSpecialWarName($special2);
        if ($genius) {

            $logger->pushGlobalActionLog("<G><b>{$cityname}</b></>?????? <Y>{$name}</>{$josaRa}??? ????????? ????????? ????????? ????????????.");
            $logger->pushGlobalActionLog("<C>{$speicalText}</> ????????? ?????? <C>??????</>??? ???????????? ??? ????????? ??????????????????.");
            $logger->pushGlobalHistoryLog("<L><b>????????????</b></><G><b>{$cityname}</b></>??? ????????? ??????????????????.");
        } else {
            $logger->pushGlobalActionLog("<G><b>{$cityname}</b></>?????? <Y>{$name}</>{$josaRa}??? ????????? ????????? ????????? ????????????.");
        }

        $logger->pushGeneralHistoryLog("<Y>{$name}</>, <G>{$cityname}</>?????? ??? ?????? ??????.");
        $logger->pushGeneralActionLog("????????? ???????????? PHP??? ????????? ?????? ?????? ??????????????? ^o^", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("?????? ????????? ???????????? <D>?????????</>??? ???????????????,", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("??????????????? ???????????? ???????????? ?????? ??????????????? ????????????~", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("?????? ????????? ????????? ????????? ???????????? ^^", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("?????? <C>$pleadership</> ?????? <C>$pstrength</> ?????? <C>$pintel</> ??? ???????????? ??????????????????.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("????????? <C>$age</>?????? ???????????????.", ActionLogger::PLAIN);

        if ($genius) {
            $logger->pushGeneralActionLog("???????????????! ????????? ????????? ???????????? <C>{$speicalText}</> ????????? ????????? ?????????!", ActionLogger::PLAIN);
            $logger->pushGeneralHistoryLog("<C>{$speicalText}</> ????????? ?????? ????????? ??????.");
        }

        $logger->flush();

        pushAdminLog(["?????? : {$userID} // {$name} // {$generalID}" . getenv("REMOTE_ADDR")]);

        $rootDB->insert('member_log', [
            'member_no' => $userID,
            'date' => TimeUtil::now(),
            'action_type' => 'make_general',
            'action' => Json::encode([
                'server' => DB::prefix(),
                'type' => 'general',
                'generalID' => $generalID,
                'generalName' => $name
            ])
        ]);

        return null;
    }
}
