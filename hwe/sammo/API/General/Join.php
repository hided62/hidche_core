<?php

namespace sammo\API\General;

use Ds\Set;
use sammo\ActionLogger;
use sammo\Auction;
use sammo\CityConst;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
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

    public function calcRestInheritPoint(int $userID): int
    {
        $db = DB::db();

        if (Util::toInt(UniqueConst::$seasonIdx) < 1) {
            //이벤트 시즌은 카운트하지 않음
            return 0;
        }

        $gameID = UniqueConst::$serverID;
        $alreadyJoined = $db->queryFirstField('SELECT count(*) FROM `storage` WHERE `namespace` = %s AND `key` LIKE %s', 'inheritance_result', "{$gameID}_{$userID}_%");
        if ($alreadyJoined) {
            //이미 받았음
            return 0;
        }

        $targetBonusPointCnt = 0;
        //정상적으로 끝난 깃수만 제공
        $oldGames = $db->queryFirstColumn('SELECT server_id FROM ng_games WHERE `winner_nation` IS NOT NULL AND `season` >= 1  AND `server_id` != %s ORDER BY `date` DESC LIMIT 8', $gameID);

        if(!$oldGames){
            return 0;
        }
        //이전에 장수를 생성한 적이 있는지
        $oldGamesCnt = new Set($db->queryFirstColumn('SELECT `server_id` FROM ng_old_generals WHERE server_id IN %ls AND `owner` = %i GROUP BY `server_id`', $oldGames, $userID));
        foreach ($oldGames as $oldGameID) {
            if($oldGamesCnt->contains($oldGameID)) {
                break;
            }
            $targetBonusPointCnt += 1;
        }
        return $targetBonusPointCnt;
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
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

        if ($inheritTurntime !== null && $inheritCity !== null) {
            return '턴과 도시를 동시에 지정할 수 없습니다.';
        }

        $rootDB = RootDB::db();
        //회원 테이블에서 정보확인
        $member = $rootDB->queryFirstRow('SELECT `no`, id, picture, grade, `name`, imgsvr FROM member WHERE no=%i', $userID);

        if (!$member) {
            return "잘못된 접근입니다!!!";
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->cacheValues(['year', 'month', 'maxgeneral', 'scenario', 'show_img_level', 'block_general_create', 'turnterm', 'turntime', 'genius', 'npcmode']);
        ########## 동일 정보 존재여부 확인. ##########

        $block_general_create = $gameStor->getValue('block_general_create');
        if($block_general_create & 1){
            return '장수 직접 생성이 불가능한 모드입니다.';
        }

        $blockCustomGeneralName = $gameStor->getValue('block_general_create') & 2;

        $gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');
        $oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);
        $oldName = $db->queryFirstField('SELECT `no` FROM general WHERE `name`=%s', $name);

        if ($oldGeneral) {
            return "이미 등록하셨습니다!";
        }
        if ($oldName) {
            return "이미 있는 장수입니다. 다른 이름으로 등록해 주세요!";
        }
        if ($gameStor->maxgeneral <= $gencount) {
            return "더이상 등록할 수 없습니다!";
        }
        if ($name == '') {
            return "이름이 짧습니다. 다시 가입해주세요!";
        }
        if (mb_strwidth($name) > 18) {
            return "이름이 유효하지 않습니다. 다시 가입해주세요!";
        }
        if ($leadership + $strength + $intel > GameConst::$defaultStatTotal) {
            return "능력치가 " . GameConst::$defaultStatTotal . "을 넘어섰습니다. 다시 가입해주세요!";
        }

        if ($inheritBonusStat) {
            if (count($inheritBonusStat) != 3) {
                return "보너스 능력치가 잘못 지정되었습니다. 다시 가입해주세요!";
            }
            foreach ($inheritBonusStat as $stat) {
                if ($stat < 0) {
                    return "보너스 능력치가 음수입니다. 다시 가입해주세요!";
                }
            }
            $sum = array_sum($inheritBonusStat);
            if ($sum == 0) {
                $inheritBonusStat = null;
            } else if ($sum < 3 || $sum > 5) {
                return "보너스 능력치 합이 잘못 지정되었습니다. 다시 가입해주세요!";
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
            return "유산 포인트가 부족합니다. 다시 가입해주세요!";
        }

        if ($inheritSpecial !== null && $gameStor->genius == 0) {
            return "이미 천재가 모두 나타났습니다. 다시 가입해주세요!";
        }

        if ($inheritCity !== null && !key_exists($inheritCity, CityConst::all())) {
            return "도시가 잘못 지정되었습니다. 다시 가입해주세요!";
        }

        if ($inheritSpecial) {
            $speicalText = getGeneralSpecialWarName($inheritSpecial);
            $userLogger->push("{$speicalText} 전투 특기를 가진 천재 생성", "inheritPoint");
            $genius = true;
        } else {
            // 현재 1%
            $genius = $rng->nextBool(0.01);
        }

        if ($genius && $gameStor->genius > 0) {
            $gameStor->genius = $gameStor->genius - 1;
        } else {
            $genius = false;
        }

        if ($inheritCity !== null) {
            $cityname = CityConst::byID($inheritCity)->name;
            $userLogger->push("{$cityname}에 장수 생성", "inheritPoint");
            $city = $inheritCity;
        } else {
            // 공백지에서만 태어나게
            $cities = $db->queryFirstColumn('SELECT city FROM city where `level`>=5 and `level`<=6 and nation=0');
            if (!$cities) {
                $cities = $db->queryFirstColumn('SELECT city FROM city where `level`>=5 and `level`<=6');
            }
            $city = $rng->choice($cities);
        }

        if ($inheritBonusStat) {
            [$pleadership, $pstrength, $pintel] = $inheritBonusStat;
            $userLogger->push("{$pleadership}, {$pstrength}, {$pintel} 보너스 능력치로 생성", "inheritPoint");
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
        // 아직 남았고 천재등록상태이면 특기 부여
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
        //내특
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

            $userLogger->push(sprintf("턴 시간 %02d:%02d 로 지정", intdiv($inheritTurntime, 60), $inheritTurntime % 60), "inheritPoint");

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

        //특회 전콘
        if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "" && $pic) {
            $face = $member['picture'];
            $imgsvr = $member['imgsvr'];
        } else {
            $face = "default.jpg";
            $imgsvr = 0;
        }

        //성격 랜덤시
        if (!in_array($character, GameConst::$availablePersonality)) {
            $character = $rng->choice(GameConst::$availablePersonality);
        }
        //상성 랜덤
        $affinity = $rng->nextRangeInt(1, 150);

        $betray = 0;
        if ($relYear >= 4) {
            $betray += 2;
        }

        if($blockCustomGeneralName){
            $name = bin2hex(random_bytes(5));
        }

        ########## 회원정보 테이블에 입력값을 등록한다. ##########
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
            'lastrefresh' => $now,
            'crewtype' => GameUnitConst::DEFAULT_CREWTYPE,
            'makelimit' => 0,
            'betray' => $betray,
            'age' => $age,
            'startage' => $age,
            'personal' => $character,
            'specage' => $specage,
            'special' => $special,
            'specage2' => $specage2,
            'special2' => $special2
        ]);
        $generalID = $db->insertId();

        if($blockCustomGeneralName){
            //XXX: 클래스가 이게 맞나?
            $name = Auction::genObfuscatedName($generalID);
            $db->update('general', [
                'name' => $name
            ], 'no=%i', $generalID);
        }

        $turnRows = [];
        foreach (Util::range(GameConst::$maxTurn) as $turnIdx) {
            $turnRows[] = [
                'general_id' => $generalID,
                'turn_idx' => $turnIdx,
                'action' => '휴식',
                'arg' => null,
                'brief' => '휴식'
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
            $userLogger->push("장수 생성으로 포인트 {$inheritRequiredPoint} 소모", "inheritPoint");
            $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$userID}");
            $inheritTotalPoint -= $inheritRequiredPoint;
            $inheritStor->setValue('previous', [$inheritTotalPoint, null]);
            $userLogger->flush();
            $db->update('rank_data', [
                'value' => $db->sqleval('value + %i', $inheritRequiredPoint)
            ], 'general_id = %i AND type = %s', $generalID, RankColumn::inherit_point_spent_dynamic->value);
        }


        $restInheritPoint = $this->calcRestInheritPoint($userID) * 500;
        if($restInheritPoint > 0){
            $userLogger->push("신규/복귀 생성으로 포인트 {$restInheritPoint} 지급", "inheritPoint");
            $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$userID}");
            $inheritTotalPoint += $restInheritPoint;
            $inheritStor->setValue('previous', [$inheritTotalPoint, null]);
            $userLogger->flush();
        }



        $logger = new ActionLogger($generalID, 0, $gameStor->year, $gameStor->month);

        $josaRa = JosaUtil::pick($name, '라');
        $speicalText = getGeneralSpecialWarName($special2);
        if ($genius) {

            $logger->pushGlobalActionLog("<G><b>{$cityname}</b></>에서 <Y>{$name}</>{$josaRa}는 기재가 천하에 이름을 알립니다.");
            $logger->pushGlobalActionLog("<C>{$speicalText}</> 특기를 가진 <C>천재</>의 등장으로 온 천하가 떠들썩합니다.");
            $logger->pushGlobalHistoryLog("<L><b>【천재】</b></><G><b>{$cityname}</b></>에 천재가 등장했습니다.");
        } else {
            $logger->pushGlobalActionLog("<G><b>{$cityname}</b></>에서 <Y>{$name}</>{$josaRa}는 호걸이 천하에 이름을 알립니다.");
        }

        $logger->pushGeneralHistoryLog("<Y>{$name}</>, <G>{$cityname}</>에서 큰 뜻을 품다.");
        $logger->pushGeneralActionLog("삼국지 모의전투 PHP의 세계에 오신 것을 환영합니다 ^o^", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("처음 하시는 경우에는 <D>도움말</>을 참고하시고,", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("문의사항이 있으시면 게시판에 글을 남겨주시면 되겠네요~", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("부디 즐거운 삼모전 되시길 바랍니다 ^^", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("통솔 <C>$pleadership</> 무력 <C>$pstrength</> 지력 <C>$pintel</> 의 보너스를 받으셨습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("연령은 <C>$age</>세로 시작합니다.", ActionLogger::PLAIN);

        if ($genius) {
            $logger->pushGeneralActionLog("축하합니다! 천재로 태어나 처음부터 <C>{$speicalText}</> 특기를 가지게 됩니다!", ActionLogger::PLAIN);
            $logger->pushGeneralHistoryLog("<C>{$speicalText}</> 특기를 가진 천재로 탄생.");
        }

        $logger->flush();

        pushAdminLog(["가입 : {$userID} // {$name} // {$generalID}" . getenv("REMOTE_ADDR")]);

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
