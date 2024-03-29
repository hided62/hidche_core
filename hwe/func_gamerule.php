<?php

namespace sammo;

use sammo\DTO\AuctionInfo;
use sammo\Enums\AuctionType;
use sammo\Enums\EventTarget;
use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;

/**
 * 게임 룰에 해당하는 함수 모음
 */

function getNationLevelList(): array
{
    $table = [
        0 => ['방랑군', 2, 0],
        1 => ['호족', 2, 1],
        2 => ['군벌', 4, 2],
        3 => ['주자사', 4, 5],
        4 => ['주목', 6, 8],
        5 => ['공', 6, 11],
        6 => ['왕', 8, 16],
        7 => ['황제', 8, 21],
    ];
    return $table;
}

function getCityLevelList(): array
{
    return [
        1 => '수',
        2 => '진',
        3 => '관',
        4 => '이',
        5 => '소',
        6 => '중',
        7 => '대',
        8 => '특'
    ];
}

//한국가의 전체 전방 설정
function SetNationFront($nationNo)
{
    if (!$nationNo) {
        return;
    }
    // 도시소유 국가와 선포,교전중인 국가

    $adj3 = [];
    $adj2 = [];
    $adj1 = [];

    $db = DB::db();
    foreach ($db->queryFirstColumn(
        'SELECT city FROM city JOIN diplomacy ON diplomacy.you = city.nation WHERE diplomacy.state = 0 AND me = %i',
        $nationNo
    ) as $city) {
        foreach (CityConst::byID($city)->path as $adjKey => $adjVal) {
            $adj3[$adjKey] = $adjVal;
        }
    };
    foreach ($db->queryFirstColumn(
        'SELECT city FROM city JOIN diplomacy ON diplomacy.you = city.nation WHERE diplomacy.state = 1 AND diplomacy.term <= 5 AND me = %i',
        $nationNo
    ) as $city) {
        foreach (CityConst::byID($city)->path as $adjKey => $adjVal) {
            $adj1[$adjKey] = $adjVal;
        }
    }
    if (!$adj3 && !$adj1) {
        //평시이면 공백지
        //NOTE: if, else일 경우 NPC는 전쟁시에는 공백지로 출병하지 않는다는 뜻이 된다.
        foreach ($db->queryFirstColumn('SELECT city from city where nation=0') as $city) {
            foreach (CityConst::byID($city)->path as $adjKey => $adjVal) {
                $adj2[$adjKey] = $adjVal;
            }
        }
    }

    $db->update('city', [
        'front' => 0
    ], 'nation=%i', $nationNo);

    if ($adj1) {
        $db->update('city', [
            'front' => 1,
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj1));
    }
    if ($adj2) {
        $db->update('city', [
            'front' => 2,
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj2));
    }
    if ($adj3) {
        $db->update('city', [
            'front' => 3,
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj3));
    }
}

function checkSupply()
{
    $db = DB::db();

    $cities = [];
    foreach ($db->query('SELECT city, nation FROM city WHERE nation != 0') as $city) {
        $newCity = new \stdClass();
        $newCity->id = Util::toInt($city['city']);
        $newCity->nation = Util::toInt($city['nation']);
        $newCity->supply = false;

        $cities[$newCity->id] = $newCity;
    }

    $queue = new \SplQueue();
    foreach ($db->queryAllLists('SELECT capital, nation FROM nation WHERE `level` > 0') as list($capitalID, $nationID)) {
        if (!key_exists($capitalID, $cities)) {
            continue;
        }
        $city = $cities[$capitalID];
        if ($nationID != $city->nation) {
            continue;
        }
        $city->supply = true;
        $queue->enqueue($city);
    }

    while (!$queue->isEmpty()) {
        $cityLink = $queue->dequeue();
        $city = CityConst::byID($cityLink->id);

        foreach (array_keys($city->path) as $connCityID) {
            if (!key_exists($connCityID, $cities)) {
                continue;
            }
            $connCity = $cities[$connCityID];
            if ($connCity->nation != $cityLink->nation) {
                continue;
            }
            if ($connCity->supply) {
                continue;
            }
            $connCity->supply = true;
            $queue->enqueue($connCity);
        }
    }

    $db->update('city', [
        'supply' => 1
    ], 'nation=0');

    $db->update('city', [
        'supply' => 0
    ], 'nation!=0');

    $supply = [];

    foreach ($cities as $city) {
        if ($city->supply) {
            $supply[] = $city->id;
        }
    }

    if ($supply) {
        $db->update('city', [
            'supply' => 1
        ], 'city IN %li', $supply);
    }
}

function updateGeneralNumber()
{
    $db = DB::db();
    foreach ($db->queryAllLists('SELECT nation, count(*) FROM general WHERE npc != 5 GROUP BY nation') as [$nationID, $gennum]) {
        if ($nationID === 0) {
            continue;
        }
        $db->update('nation', [
            'gennum' => $gennum
        ], 'nation=%i', $nationID);
    }
    refreshNationStaticInfo();
}

// 벌점 감소와 건국제한-1 전턴제한-1 외교제한-1, 1달마다 실행, 병사 있는 장수의 군량 감소, 수입비율 조정
function preUpdateMonthly()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    //연감 월결산
    $result = LogHistory();

    if ($result == false) {
        return false;
    }


    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    //접률감소, 건국제한-1
    $db->update('general_access_log', [
        'refresh_score_total' => $db->sqleval('floor(refresh_score_total*0.99)'),
    ], true);
    $db->update('general', [
        'makelimit' => $db->sqleval('greatest(0, makelimit - 1)'),
    ], true);
    //전략제한-1, 외교제한-1, 세율동기화
    $db->update('nation', [
        'strategic_cmd_limit' => $db->sqleval('greatest(0, strategic_cmd_limit - 1)'),
        'surlimit' => $db->sqleval('greatest(0, surlimit - 1)'),
        'rate_tmp' => $db->sqleval('rate')
    ], true);

    // 20 ~ 140원
    $develcost = ($admin['year'] - $admin['startyear'] + 10) * 2;
    $gameStor->develcost = $develcost;

    //계략, 전쟁표시 해제
    $db->update('city', [
        'state' => $db->sqleval(<<<EOD
(CASE
WHEN state=31 THEN 0
WHEN state=32 THEN 31
WHEN state=33 THEN 0
WHEN state=34 THEN 33
WHEN state=41 THEN 0
WHEN state=42 THEN 41
WHEN state=43 THEN 42
ELSE state END)
EOD),
        'term' => $db->sqleval('greatest(0, term - 1)'),
        'conflict' => $db->sqleval('if(term = 0,%s,conflict)', '{}'),
    ], true);

    //첩보-1
    foreach ($db->queryAllLists("SELECT nation, spy FROM nation WHERE spy!='' AND spy!='{}'") as [$nationNo, $rawSpy]) {
        $spyInfo = Json::decode($rawSpy);

        foreach ($spyInfo as $cityNo => $remainMonth) {
            if ($remainMonth <= 1) {
                unset($spyInfo[$cityNo]);
            } else {
                $spyInfo[$cityNo] -= 1;
            }
        }

        $db->update('nation', [
            'spy' => Json::encode($spyInfo, Json::EMPTY_ARRAY_IS_DICT)
        ], 'nation=%i', $nationNo);
    }

    return true;
}

// 외교 로그처리, 외교 상태 처리
function postUpdateMonthly(RandUtil $rng)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'scenario']);
    $globalLogger = new ActionLogger(0, 0, $admin['year'], $admin['month']);

    //도시 수 측정
    $cityNations = [];
    foreach ($db->queryAllLists('SELECT city, name, nation FROM city') as [$cityID, $cityName, $cityNation]) {
        if (!key_exists($cityNation, $cityNations)) {
            $cityNations[$cityNation] = [];
        }
        $cityNations[$cityNation][] = $cityName;
    }

    //각 국가 전월 장수수 대비 당월 장수수로 단합도 산정
    //각 국가 장수수를 구하고 국력 산정
    //    $query = "select nation,gennum from nation where level>0";
    // 국력=
    // 자원(국가/장수의 금,쌀)
    // 기술력
    // 인구수*내정%
    // 장수능력
    // 접속률
    // 숙련도
    // 명성,공헌
    $nations = Util::convertArrayToDict($db->query('SELECT
    A.nation,
    A.gennum,
    round((
        round(((A.gold+A.rice)+(select sum(gold+rice) from general where nation=A.nation))/100)
        +A.tech
        +if(A.level=0,0,(
            select round(
                sum(pop)*sum(pop+agri+comm+secu+wall+def)/sum(pop_max+agri_max+comm_max+secu_max+wall_max+def_max)/100
            ) from city where nation=A.nation and supply=1
        ))
        +(select SUM((ra.value + 1000)/(rb.value + 1000)*(CASE WHEN g.`npc` < 2 THEN 1.2 ELSE 1 END)*(CASE WHEN g.`leadership` >= 40 THEN g.`leadership` ELSE 0 END)*2 + (SQRT(g.intel * g.strength) * 2 + g.leadership / 2)/2)
                FROM general AS g
                LEFT JOIN `rank_data` AS ra ON g.`no` = ra.general_id AND ra.`type` = \'killcrew_person\'
                LEFT JOIN `rank_data` AS rb ON g.`no` = rb.general_id AND rb.`type` = \'deathcrew_person\'
                WHERE g.nation = A.nation)
        +(select round(sum(dex1+dex2+dex3+dex4+dex5)/1000) from general where nation=A.nation)
        +(select round(sum(experience+dedication)/100) from general where nation=A.nation)
    )/10)
    as power,
    (select sum(crew) from general where nation=A.nation) as totalCrew
    from nation A
    group by A.nation'), 'nation');
    $maxPowerValues = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'max_power');

    foreach ($nations as $nation) {
        $nationID = $nation['nation'];
        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $genNum[$nationID] = $nation['gennum'];

        $powerValues = $maxPowerValues[$nationID] ?? [];

        //약간의 랜덤치 부여 (95% ~ 105%)

        $nation['power'] = Util::round($nation['power'] * $rng->nextRange(0.95, 1.05));
        $powerValues['maxPower'] = max($powerValues['maxPower'] ?? 0, $nation['power']);
        $powerValues['maxCrew'] = max($powerValues['maxCrew'] ?? 0, Util::toInt($nation['totalCrew']));

        if (count($cityNations[$nationID] ?? []) > count($powerValues['maxCities'] ?? [])) {
            $powerValues['maxCities'] = $cityNations[$nationID];
        }

        $db->update('nation', [
            'power' => $nation['power']
        ], 'nation=%i', $nationID);
        $nationStor->max_power = $powerValues;
    }

    // 전쟁기한 세팅
    foreach ($db->query('SELECT me, you, dead, term FROM diplomacy WHERE state = 0') as $dip) {
        $genCount = $genNum[$dip['me']];
        // 25% 참여율일때 두당 10턴에 4000명 소모한다고 계산
        // 4000 / 10 * 0.25 = 100
        $term = floor($dip['dead'] / 100 / $genCount);
        $dip['dead'] -= $term * 100 * $genCount;
        $term = Util::valueFit($dip['term'] + $term, 0, 13);

        $db->update('diplomacy', [
            'term' => $term,
            'dead' => $dip['dead'],
        ], 'me = %i AND you = %i', $dip['me'], $dip['you']);
    }

    //개전국 로그
    foreach ($db->query('SELECT me, you FROM diplomacy WHERE state = 1 AND term <= 1 AND me < you') as $dip) {
        $nation1 = getNationStaticInfo($dip['me']);
        $name1 = $nation1['name'];
        $nation2 = getNationStaticInfo($dip['you']);
        $name2 = $nation2['name'];

        $josaYi = JosaUtil::pick($name2, '이');
        $josaWa = JosaUtil::pick($name1, '와');
        $globalLogger->pushGlobalHistoryLog("<R><b>【개전】</b></><D><b>$name1</b></>{$josaWa} <D><b>$name2</b></>{$josaYi} <R>전쟁</>을 시작합니다.");
    }
    //휴전국 로그
    $stopWarList = [];
    foreach ($db->queryAllLists('SELECT me,you FROM diplomacy WHERE state=0 AND term <= 1 ORDER BY me desc, you desc') as [$me, $you]) {
        if ($me < $you) {
            $key = "{$me}_{$you}";
        } else {
            $key = "{$you}_{$me}";
        }
        if (!key_exists($key, $stopWarList)) {
            $stopWarList[$key] = true;
            continue;
        }

        //양측 기간 모두 0이 되는 상황이면 휴전
        $nation1 = getNationStaticInfo($me);
        $name1 = $nation1['name'];
        $nation2 = getNationStaticInfo($you);
        $name2 = $nation2['name'];

        $josaWa = JosaUtil::pick($name1, '와');
        $josaYi = JosaUtil::pick($name2, '이');

        $globalLogger->pushGlobalHistoryLog("<R><b>【휴전】</b></><D><b>$name1</b></>{$josaWa} <D><b>$name2</b></>{$josaYi} <S>휴전</>합니다.");
        $db->update('diplomacy', [
            'state' => 2,
            'term' => 0,
        ], '(me=%i AND you=%i) OR (you=%i AND me=%i)', $me, $you, $me, $you);
    }

    $globalLogger->flush();

    //사상자 초기화, 외교 기한-1
    $db->update('diplomacy', [
        'dead' => $db->sqleval('if(state!=0, 0, dead)'),
        'term' => $db->sqleval('greatest(0, term-1)'),
    ], true);
    //불가침 끝나면 통상으로
    $db->update('diplomacy', [
        'state' => 2,
    ], 'state = 7 AND term = 0');
    //선포 끝나면 교전으로
    $db->update('diplomacy', [
        'state' => 0,
        'term' => 6,
    ], 'state = 1 AND term = 0');

    $availableWarSettingCnt = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'available_war_setting_cnt');
    foreach ($nations as $nation) {
        $nationID = $nation['nation'];
        if (!key_exists($nationID, $availableWarSettingCnt)) {
            $availableWarSettingCnt[$nationID] = 0;
        }
    }
    foreach ($availableWarSettingCnt as $nationID => $cnt) {
        if ($cnt >= GameConst::$maxAvailableWarSettingCnt) {
            continue;
        }
        $cnt = Util::valueFit($cnt + GameConst::$incAvailableWarSettingCnt, 0, GameConst::$maxAvailableWarSettingCnt);
        (KVStorage::getStorage($db, $nationID, 'nation_env'))->setValue('available_war_setting_cnt', $cnt);
    }

    //초반이후 방랑군 자동 해체
    if ($admin['year'] >= $admin['startyear'] + 2) {
        checkWander($rng);
    }
    updateGeneralNumber();
    refreshNationStaticInfo();
    // 천통여부 검사
    checkEmperior();
    //토너먼트 개시
    triggerTournament($rng);
    // 시스템 거래건 등록
    registerAuction($rng);
    //전방설정
    foreach (getAllNationStaticInfo() as $nation) {
        if ($nation['level'] <= 0) {
            continue;
        }
        SetNationFront($nation['nation']);
    }
}


function checkWander(RandUtil $rng)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year', 'month', 'init_year', 'init_month']);

    $wanderers = $db->queryFirstColumn('SELECT general.`no` FROM general LEFT JOIN nation ON general.nation = nation.nation WHERE nation.`level` = 0 AND general.`officer_level` = 12');

    foreach (General::createObjListFromDB($wanderers) as $wanderer) {
        $wanderCmd = buildGeneralCommandClass('che_해산', $wanderer, $admin);
        if ($wanderCmd->hasFullConditionMet()) {
            $logger = $wanderer->getLogger();
            $logger->pushGeneralActionLog('초반 제한후 방랑군은 자동 해산됩니다.', ActionLogger::PLAIN);
            $wanderCmd->run($rng);
            $wanderCmd->setNextAvailable();
        }
    }

    if ($wanderers) {
        refreshNationStaticInfo();
    }
}

function checkStatistic()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year', 'month']);

    $nationHists = [];
    $specialHists = [];
    $personalHists = [];
    $specialHists2 = [];
    $crewtypeHists = [];

    $etc = '';

    $auxData = [
        'generals' => [],
        'nations' => [],
    ];

    $avgGeneral = $db->queryFirstRow(
        'SELECT avg(gold) as avggold, avg(rice) as avgrice, avg(dex1+dex2+dex3+dex4) as avgdex,
        max(dex1+dex2+dex3+dex4) as maxdex, avg(experience+dedication) as avgexpded, max(experience+dedication) as maxexpded
        FROM general'
    );
    $auxData['generals']['avg'] = $avgGeneral;

    $avgGeneral['avggold'] = Util::round($avgGeneral['avggold']);
    $avgGeneral['avgrice'] = Util::round($avgGeneral['avgrice']);
    $avgGeneral['avgdex'] = Util::round($avgGeneral['avgdex']);
    $avgGeneral['avgexpded'] = Util::round($avgGeneral['avgexpded']);
    $etc .= "평균 금/쌀 ({$avgGeneral['avggold']}/{$avgGeneral['avgrice']}), 평균/최고 숙련({$avgGeneral['avgdex']}/{$avgGeneral['maxdex']}), 평균/최고 경험공헌({$avgGeneral['avgexpded']}/{$avgGeneral['maxexpded']}), ";

    $avgNation = $db->queryFirstRow(
        'SELECT min(tech) as mintech, max(tech) as maxtech, avg(tech) as avgtech,
        min(power) as minpower, max(power) as maxpower, avg(power) as avgpower from nation where level>0'
    );
    $auxData['nations']['avg'] = $avgNation;

    $avgNation['mintech'] = floor($avgNation['mintech']);
    $avgNation['maxtech'] = floor($avgNation['maxtech']);
    $avgNation['avgtech'] = Util::round($avgNation['avgtech']);
    $avgNation['avgpower'] = Util::round($avgNation['avgpower']);
    $etc .= "최저/평균/최고 기술({$avgNation['mintech']}/{$avgNation['avgtech']}/{$avgNation['maxtech']}), ";
    $etc .= "최저/평균/최고 국력({$avgNation['minpower']}/{$avgNation['avgpower']}/{$avgNation['maxpower']}), ";

    $nationName = '';
    $powerHist = '';

    $nations = Util::convertArrayToDict(
        $db->query(
            'SELECT nation,name,type,power,gennum,gold+rice as goldrice from nation where level>0 order by power desc',
            'nation'
        ),
        'nation'
    );
    $nationCount = count($nations);

    $nationGeneralInfos = Util::convertArrayToDict(
        $db->query(
            'SELECT nation, sum(leadership+strength+intel) as abil,sum(gold+rice) as goldrice,
            sum(dex1+dex2+dex3+dex4) as dex,sum(experience+dedication) as expded
            from general GROUP BY nation'
        ),
        'nation'
    );

    $nationCityInfos = Util::convertArrayToDict(
        $db->query('SELECT nation, count(*) as cnt, sum(pop) as pop,sum(pop_max) as pop_max from city GROUP BY nation'),
        'nation'
    );

    foreach ($nations as $nationNo => &$nation) {
        $general = $nationGeneralInfos[$nationNo];
        $city = $nationCityInfos[$nationNo];

        $nation['generalInfo'] = $general;
        $nation['cityInfo'] = $city;

        $nationName .= $nation['name'] . '(' . getNationType($nation['type']) . '), ';
        $powerHist .= "{$nation['name']}({$nation['power']}/{$nation['gennum']}/{$city['cnt']}/{$city['pop']}/{$city['pop_max']}/{$nation['goldrice']}/{$general['goldrice']}/{$general['abil']}/{$general['dex']}/{$general['expded']}), ";

        if (!isset($nationHists[$nation['type']])) {
            $nationHists[$nation['type']] = 0;
        }
        $nationHists[$nation['type']]++;
    }
    unset($nation);

    $auxData['nations']['all'] = $nations;

    $nationHist = '';
    foreach (GameConst::$availableNationType as $nationType) {
        if (!Util::array_get($nationHists[$nationType])) {
            $nationHists[$nationType] = '-';
        }
        $nationHist .= getNationType($nationType) . "({$nationHists[$nationType]}), ";
    }

    $generals = $db->query('SELECT `no`,npc,personal,special,special2,crewtype FROM general');

    $genCount = 0;
    $npcCount = 0;
    $generalCount = count($generals);

    foreach ($generals as $general) {
        if (!isset($personalHists[$general['personal']])) {
            $personalHists[$general['personal']] = 0;
        }

        if (!isset($specialHists[$general['special']])) {
            $specialHists[$general['special']] = 0;
        }

        if (!isset($specialHists2[$general['special2']])) {
            $specialHists2[$general['special2']] = 0;
        }

        if ($general['npc'] < 2) {
            $genCount += 1;
        } else {
            $npcCount += 1;
        }

        $personalHists[$general['personal']]++;
        $specialHists[$general['special']]++;
        $specialHists2[$general['special2']]++;
    }

    foreach ($db->queryAllLists(
        'SELECT crewtype, count(crewtype) AS cnt FROM general WHERE recent_war != NULL GROUP BY crewtype'
    ) as [$crewtype, $cnt]) {
        $crewtypeHists[$crewtype] = $cnt;
    }

    $auxData['generals']['hists'] = [
        'personal' => $personalHists,
        'special' => $specialHists,
        'special2' => $specialHists2,
        'crewtype' => $crewtypeHists,
        'userCnt' => $genCount,
        'npcCnt' => $npcCount,
    ];

    $generalCountStr = "{$generalCount}({$genCount}+{$npcCount})";

    $personalHistStr = join(', ', array_map(function ($histPair) {
        [$histKey, $cnt] = $histPair;
        return getGenChar($histKey) . '(' . $cnt . ')';
    }, Util::convertDictToArray($personalHists)));

    $specialHistsStr = join(', ', array_map(function ($histPair) {
        [$histKey, $cnt] = $histPair;
        return getGeneralSpecialDomesticName($histKey) . '(' . $cnt . ')';
    }, Util::convertDictToArray($specialHists)));

    $specialHists2Str = join(', ', array_map(function ($histPair) {
        [$histKey, $cnt] = $histPair;
        return getGeneralSpecialWarName($histKey) . '(' . $cnt . ')';
    }, Util::convertDictToArray($specialHists2)));

    $specialHistsAllStr = "$specialHistsStr // $specialHists2Str";

    $crewtypeHistsStr = join(', ', array_map(function ($histPair) {
        [$histKey, $cnt] = $histPair;
        return GameUnitConst::byID($histKey)->getShortName() . '(' . $cnt . ')';
    }, Util::convertDictToArray($crewtypeHists)));

    $db->insert('statistic', [
        'year' => $admin['year'],
        'month' => $admin['month'],
        'nation_count' => $nationCount,
        'nation_name' => $nationName,
        'nation_hist' => $nationHist,
        'gen_count' => $generalCountStr,
        'personal_hist' => $personalHistStr,
        'special_hist' => $specialHistsAllStr,
        'power_hist' => $powerHist,
        'crewtype' => $crewtypeHistsStr,
        'etc' => $etc,
        'aux' => Json::encode($auxData)
    ]);
}


function convForOldGeneral(array $general, int $year, int $month)
{
    $general['history'] = getGeneralHistoryLogAll($general['no']);
    return [
        'server_id' => UniqueConst::$serverID,
        'general_no' => $general['no'],
        'owner' => $general['owner'],
        'name' => $general['name'],
        'last_yearmonth' => $year * 100 + $month,
        'turntime' => $general['turntime'],
        'data' => Json::encode($general)
    ];
}

function storeOldGeneral(int $no, int $year, int $month)
{
    $db = DB::db();
    $general = $db->queryFirstRow('SELECT * FROM general WHERE `no` = %i', $no);
    if (!$general) {
        return;
    }
    $data = convForOldGeneral($general, $year, $month);
    $db->insertUpdate(
        'ng_old_generals',
        $data,
        $data
    );
}

function storeOldGenerals(int $nation, int $year, int $month)
{
    $db = DB::db();
    foreach ($db->query('SELECT * FROM general WHERE nation = %i', $nation) as $general) {
        $data = convForOldGeneral($general, $year, $month);
        $db->insertUpdate(
            'ng_old_generals',
            $data,
            $data
        );
    }
}

function checkEmperior()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year', 'month', 'isunited', 'refreshLimit']);
    if ($admin['isunited'] != 0) {
        return;
    }

    $remainNations = $db->queryFirstColumn('SELECT nation FROM nation WHERE level > 0 LIMIT 2');

    if (!$remainNations || count($remainNations) != 1) {
        return;
    }

    $nationID = $remainNations[0];

    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');

    $cityCnt = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i', $nationID);
    if (!$cityCnt) {
        return;
    }

    if ($cityCnt != count(CityConst::all())) {
        return;
    }

    checkStatistic();

    $nation =  $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $nationID);
    $nationName = $nation['name'];

    $josaYi = JosaUtil::pick($nationName, '이');

    $nationLogger = new ActionLogger(0, $nationID, $admin['year'], $admin['month']);
    $nationLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>{$josaYi} 전토를 통일");

    /** @var int[] */
    $auctionList = $db->queryFirstColumn(
        'SELECT `id` FROM `ng_auction` WHERE `type` = %s AND `finished` = 0 ORDER BY `close_date` ASC',
        AuctionType::UniqueItem->value
    );
    foreach($auctionList as $auctionID){
        $auction = new AuctionUniqueItem($auctionID, new DummyGeneral(true));
        $auction->closeAuction(true);
    }

    $inheritPointManager = InheritancePointManager::getInstance();
    $allUserGenerals = General::createObjListFromDB($db->queryFirstColumn('SELECT `no` FROM general WHERE npc < 2'));
    foreach ($allUserGenerals as $genObj) {
        if ($genObj->getNationID() == $nationID) {
            if ($genObj->getVar('officer_level') > 4) {
                $genObj->increaseInheritancePoint(InheritanceKey::unifier, 2000);
            };
        }
    }

    TurnExecutionHelper::runEventHandler($db, $gameStor, EventTarget::United);

    foreach ($allUserGenerals as $genObj) {
        $inheritPointManager->mergeTotalInheritancePoint($genObj);
        $inheritPointManager->applyInheritanceUser($genObj->getVar('owner'));
    }

    $gameStor->isunited = 2;
    $gameStor->refreshLimit = $gameStor->refreshLimit * 100;

    foreach ($db->queryFirstColumn('SELECT no FROM general WHERE npc<2 AND age>=%i', GameConst::$minPushHallAge) as $hallGeneralNo) {
        CheckHall($hallGeneralNo);
    }

    [$totalPop, $totalMaxPop] = $db->queryFirstList('SELECT SUM(pop), SUM(pop_max) FROM city');
    $pop = "{$totalPop} / {$totalMaxPop}";
    $poprate = round($totalPop / $totalMaxPop * 100, 2) . " %";

    $chiefs = Util::convertArrayToDict(
        $db->query(
            'SELECT no,npc,name,picture,belong,officer_level FROM general WHERE nation=%i AND officer_level >= 5',
            $nationID
        ),
        'officer_level'
    );

    $nationGenerals = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $nationID);
    $nation['generals'] = $nationGenerals;

    $tigers = $db->query(
        'SELECT value, name
        FROM rank_data LEFT JOIN general ON rank_data.general_id = general.no
        WHERE rank_data.nation_id = %i AND rank_data.type = "killnum" AND value > 0 ORDER BY value DESC LIMIT 5',
        $nationID
    ); // 오호장군

    $tigerstr = join(', ', array_map(function ($arr) {
        $number = number_format($arr['value']);
        return "{$arr['name']}【{$number}】";
    }, $tigers));

    $eagles = $db->query(
        'SELECT value, name
        FROM rank_data LEFT JOIN general ON rank_data.general_id = general.no
        WHERE rank_data.nation_id = %i AND rank_data.type = "firenum" AND value > 0 ORDER BY value DESC LIMIT 7',
        $nationID
    ); // 건안칠자

    $eaglestr = join(', ', array_map(function ($arr) {
        $number = number_format($arr['value']);
        return "{$arr['name']}【{$number}】";
    }, $eagles));

    $rawGeneralList = $db->query('SELECT no, name, npc, owner FROM general WHERE nation=%i ORDER BY dedication DESC', $nationID);
    foreach ($rawGeneralList as $rawGeneral) {
        $generalLogger = new ActionLogger($rawGeneral['no'], $nationID, $admin['year'], $admin['month']);
        $generalLogger->pushGeneralActionLog("<D><b>{$nationName}</b></>{$josaYi} 전토를 통일하였습니다.", ActionLogger::YEAR_MONTH);
        $generalLogger->flush();
    }

    $gen = join(', ', array_column($rawGeneralList, 'name'));

    $stat = $db->queryFirstRow('SELECT max(nation_count) as nc, max(gen_count) as gc FROM statistic');
    $genCnt = $db->queryFirstField('SELECT count(*) FROM general');

    $statNC = "1 / {$stat['nc']}";
    $statGC = "{$genCnt} / {$stat['gc']}";
    $statNation = $db->queryFirstRow('SELECT nation_count,nation_name,nation_hist from statistic where nation_count=%i LIMIT 1', $stat['nc']);
    $statGeneral = $db->queryFirstRow('SELECT gen_count,personal_hist,special_hist,aux from statistic order by no desc LIMIT 1');

    $nation = $nation;
    $nation['generals'] = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $nation['nation']);
    $nation['aux'] = Json::decode($nation['aux']);
    $nation['msg'] = $nationStor->nationNotice['msg'] ?? '';;
    $nation['scout_msg'] = $nationStor->scout_msg;
    $nation['aux'] += $nationStor->max_power ?? [];
    $nation['history'] = getNationHistoryLogAll($nation['nation']);

    storeOldGenerals(0, $admin['year'], $admin['month']);
    storeOldGenerals($nation['nation'], $admin['year'], $admin['month']);

    $db->insert('ng_old_nations', [
        'server_id' => UniqueConst::$serverID,
        'nation' => $nation['nation'],
        'data' => Json::encode($nation)
    ]);

    $noNationGeneral = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=0');
    $db->insert('ng_old_nations', [
        'server_id' => UniqueConst::$serverID,
        'nation' => 0,
        'data' => Json::encode([
            'nation' => 0,
            'name' => '재야',
            'generals' => $noNationGeneral
        ])
    ]);

    $nationHistory = getNationHistoryLogAll($nation['nation']);

    $serverCnt = $db->queryFirstField('SELECT count(*) FROM ng_games');
    $serverName = UniqueConst::$serverName;

    $db->update('ng_games', [
        'winner_nation' => $nation['nation']
    ], 'server_id=%s', UniqueConst::$serverID);

    $db->insert('emperior', [
        'phase' => $serverName . $serverCnt . '기',
        'server_id' => UniqueConst::$serverID,
        'nation_count' => $statNC,
        'nation_name' => $statNation['nation_name'],
        'nation_hist' => $statNation['nation_hist'],
        'gen_count' => $statGC,
        'personal_hist' => $statGeneral['personal_hist'],
        'special_hist' => $statGeneral['special_hist'],
        'name' => $nation['name'],
        'type' => $nation['type'],
        'color' => $nation['color'],
        'year' => $admin['year'],
        'month' => $admin['month'],
        'power' => $nation['power'],
        'gennum' => $nation['gennum'],
        'citynum' => $cityCnt,
        'pop' => $pop,
        'poprate' => $poprate,
        'gold' => $nation['gold'],
        'rice' => $nation['rice'],
        'l12name' => $chiefs[12]['name'],
        'l12pic' => $chiefs[12]['picture'],
        'l11name' => $chiefs[11]['name'],
        'l11pic' => $chiefs[11]['picture'],
        'l10name' => $chiefs[10]['name'],
        'l10pic' => $chiefs[10]['picture'],
        'l9name' => $chiefs[9]['name'],
        'l9pic' => $chiefs[9]['picture'],
        'l8name' => $chiefs[8]['name'],
        'l8pic' => $chiefs[8]['picture'],
        'l7name' => $chiefs[7]['name'],
        'l7pic' => $chiefs[7]['picture'],
        'l6name' => $chiefs[6]['name'],
        'l6pic' => $chiefs[6]['picture'],
        'l5name' => $chiefs[5]['name'],
        'l5pic' => $chiefs[5]['picture'],
        'tiger' => $tigerstr,
        'eagle' => $eaglestr,
        'gen' => $gen,
        'history' => JSON::encode($nationHistory),
        'aux' => $statGeneral['aux']
    ]);

    $hiddenSeed = UniqueConst::$hiddenSeed;
    $history = ["<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통일】</b></><D><b>{$nation['name']}</b></>{$josaYi} 전토를 통일하였습니다. <span class='hidden_but_copyable'>(서버시드: {$hiddenSeed})</span>"];
    pushGlobalHistoryLog($history, $admin['year'], $admin['month']);

    //연감 월결산
    LogHistory();

    $availableInvaderGame = false;
    foreach(CityConst::all() as $city){
        if($city->level == 4){
            $availableInvaderGame = true;
            break;
        }
    }
    if($availableInvaderGame){
        $invaderMsgCnt = 2;
        foreach(range(12, 5, -1) as $chiefLevel){
            if(!key_exists($chiefLevel, $chiefs)){
                continue;
            }
            $targetChief = $chiefs[$chiefLevel];
            if($targetChief['npc'] >= 2){
                continue;
            }
            $invaderMsgs = RaiseInvaderMessage::buildRaiseInvaderMessage($targetChief['no']);
            foreach($invaderMsgs as $invaderMsg){
                $invaderMsg->send();
            }
            $invaderMsgCnt--;
            if($invaderMsgCnt <= 0){
                break;
            }
        }
    }
}

function updateMaxDomesticCritical(General $general, $score)
{
    $maxDomesticCritical = $general->getAuxVar(InheritanceKey::max_domestic_critical->value) ?? 0;
    $maxDomesticCritical += $score / 2;
    $general->setAuxVar(InheritanceKey::max_domestic_critical->value, $maxDomesticCritical);

    $oldMaxDomesticCritical = $general->getInheritancePoint(InheritanceKey::max_domestic_critical);
    if ($maxDomesticCritical > $oldMaxDomesticCritical) {
        $general->setInheritancePoint(InheritanceKey::max_domestic_critical, $maxDomesticCritical);
    }
}

function genGenericUniqueRNG(int $year, int $month, int $generalID): RandUtil
{
    return new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
        UniqueConst::$hiddenSeed,
        'unique',
        $year,
        $month,
        $generalID
    )));
}

function genGenericUniqueRNGFromGeneral(General $general): RandUtil
{
    $logger = $general->getLogger();
    if (!$logger) {
        throw new \Exception('정식 초기화된 객체가 아닙니다.');
    }
    $year = $logger->getYear();
    $month = $logger->getMonth();
    $generalID = $general->getID();
    return genGenericUniqueRNG($year, $month, $generalID);
}
