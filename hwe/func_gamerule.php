<?php
namespace sammo;

use Monolog\Logger;

/**
 * 게임 룰에 해당하는 함수 모음
 */

function getNationLevelList():array{
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

function getNationTypeList():array{
    return GameConst::$availableNationType;
    
    $table = [
        13=>['명가', '기술↑ 인구↑', '쌀수입↓ 수성↓'],
        12=>['음양가', '내정↑ 인구↑', '기술↓ 전략↓'],
        11=>['종횡가', '전략↑ 수성↑', '금수입↓ 내정↓'],
        10=>['불가', '민심↑ 수성↑', '금수입↓'],
        9=>['도적', '계략↑', '금수입↓ 치안↓ 민심↓'],
        8=>['오두미도', '쌀수입↑ 인구↑', '기술↓ 수성↓ 내정↓'],
        7=>['태평도', '인구↑ 민심↑', '기술↓ 수성↓'],
        6=>['도가', '인구↑', '기술↓ 치안↓'],
        5=>['묵가', '수성↑', '기술↓'],
        4=>['덕가', '치안↑인구↑ 민심↑', '쌀수입↓ 수성↓'],
        3=>['병가', '기술↑ 수성↑', '인구↓ 민심↓'],
        2=>['유가', '내정↑ 민심↑', '쌀수입↓'],
        1=>['법가', '금수입↑ 치안↑', '인구↓ 민심↓'],
    ];
    return $table;
}

function getCityLevelList():array{
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

function getGenDex($general, $type) {
    //XXX: 지금은 동작하지만.. 병종 구성이 보궁기귀차 에서 바뀌면...
    $ntype = GameUnitConst::byId($type)->armType * 10;
    return $general["dex{$ntype}"]??0;
}

function addGenDex($no, $atmos, $train, $type, $exp) {
    //XXX: 지금은 동작하지만.. 병종 구성이 보궁기귀차 에서 바뀌면...
    $db = DB::db();

    $armType = GameUnitConst::byId($type)->armType;
    if($armType == GameUnitConst::T_CASTLE){
        $armType = GameUnitConst::T_SIEGE;
    }

    if($armType < 0){
        return;
    }
    
    $ntype = $armType*10;
    $dexType = "dex{$ntype}";
    if($armType == GameUnitConst::T_WIZARD) {
        $exp = Util::round($exp * 0.90); 
    }
    else if($armType == GameUnitConst::T_SIEGE) {
        $exp = Util::round($exp * 0.90);
    }
    $exp = Util::round($exp * ($atmos + $train) / 200); // 사기 + 훈련 / 200

    $db->update('general', [
        $dexType=>$db->sqleval('%b + %i', $dexType, $exp)
    ], 'no=%i', $no);
}


//한국가의 전체 전방 설정
function SetNationFront($nationNo) {
    if(!$nationNo) { return; }
    // 도시소유 국가와 선포,교전중인 국가
    
    $adj3 = [];
    $adj2 = [];
    $adj1 = [];

    $db = DB::db();
    foreach($db->queryFirstColumn(
        'SELECT city FROM city JOIN diplomacy ON diplomacy.you = city.nation WHERE diplomacy.state = 0 AND me = %i'
        , $nationNo
    ) as $city){
        foreach(CityConst::byID($city)->path as $adjKey=>$adjVal){
            $adj3[$adjKey] = $adjVal;
        }
    };
    foreach($db->queryFirstColumn(
        'SELECT city FROM city JOIN diplomacy ON diplomacy.you = city.nation WHERE diplomacy.state = 1 AND diplomacy.term <= 5 AND me = %i'
        , $nationNo
    ) as $city){
        foreach(CityConst::byID($city)->path as $adjKey=>$adjVal){
            $adj1[$adjKey] = $adjVal;
        }
    }
    if(!$adj3 && !$adj1){
        //평시이면 공백지
        //NOTE: if, else일 경우 NPC는 전쟁시에는 공백지로 출병하지 않는다는 뜻이 된다.
        foreach ($db->queryFirstColumn('SELECT city from city where nation=0') as $city) {
            foreach(CityConst::byID($city)->path as $adjKey=>$adjVal){
                $adj2[$adjKey] = $adjVal;
            }
        }
    }

    $db->update('city', [
        'front'=>0
    ], 'nation=%i', $nationNo);

    if($adj1){
        $db->update('city', [
            'front'=>1,
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj1));
    }
    if($adj2){
        $db->update('city', [
            'front'=>2,
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj2));
    }
    if($adj3){
        $db->update('city', [
            'front'=>3,
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj3));
    }
}

function checkSupply() {
    $db = DB::db();

    $cities = [];
    foreach($db->query('SELECT city, nation FROM city WHERE nation != 0') as $city){
        $newCity = new \stdClass();
        $newCity->id = Util::toInt($city['city']);
        $newCity->nation = Util::toInt($city['nation']);
        $newCity->supply = false;

        $cities[$newCity->id] = $newCity;
    }
    
    $queue = new \SplQueue();
    foreach($db->queryAllLists('SELECT capital, nation FROM nation WHERE `level` > 0') as list($capitalID, $nationID)){
        if(!key_exists($capitalID, $cities)){
            continue;
        }
        $city = $cities[$capitalID];
        if($nationID != $city->nation){
            continue;
        }
        $city->supply = true;
        $queue->enqueue($city);
    }

    while(!$queue->isEmpty()){
        $cityLink = $queue->dequeue();
        $city = CityConst::byID($cityLink->id);

        foreach(array_keys($city->path) as $connCityID){
            if(!key_exists($connCityID, $cities)){
                continue;
            }
            $connCity = $cities[$connCityID];
            if($connCity->nation != $cityLink->nation){
                continue;
            }
            if($connCity->supply){
                continue;
            }
            $connCity->supply = true;
            $queue->enqueue($connCity);
        }
    }

    $db->update('city',[
        'supply'=>1
    ], 'nation=0');

    $db->update('city',[
        'supply'=>0
    ], 'nation!=0');

    $supply = [];

    foreach($cities as $city){
        if($city->supply){
            $supply[] = $city->id;
        }
    }

    if($supply){
        $db->update('city', [
            'supply'=>1
        ], 'city IN %li', $supply);
    }

}


function updateYearly() {
    //통계
    checkStatistic();
}

//관직 변경 해제
function updateQuaterly() {
    $db = DB::db();

    //천도 제한 해제, 관직 변경 제한 해제
    $db->update('nation', [
        'l12set'=>0,
        'l11set'=>0,
        'l10set'=>0,
        'l9set'=>0,
        'l8set'=>0,
        'l7set'=>0,
        'l6set'=>0,
        'l5set'=>0,
    ], true);
    //관직 변경 제한 해제
    $db->update('city', [
        'officer4set'=>0,
        'officer3set'=>0,
        'officer2set'=>0,
    ], true);
}

// 벌점 감소와 건국제한-1 전턴제한-1 외교제한-1, 1달마다 실행, 병사 있는 장수의 군량 감소, 수입비율 조정
function preUpdateMonthly() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    //연감 월결산
    $result = LogHistory();

    if($result == false) { return false; }
    

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);
    $logger = new ActionLogger(0, 0, $admin['year'], $admin['month']);

    //보급선 체크
    checkSupply();
    //미보급도시 10% 감소
    $db->update('city', [
        'pop'=>$db->sqleval('pop * 0.9'),
        'trust'=>$db->sqleval('trust * 0.9'),
        'agri'=>$db->sqleval('agri * 0.9'),
        'comm'=>$db->sqleval('comm * 0.9'),
        'secu'=>$db->sqleval('secu * 0.9'),
        'def'=>$db->sqleval('def * 0.9'),
        'wall'=>$db->sqleval('wall * 0.9'),
    ], 'supply = 0');
    //미보급도시 장수 병 훈 사 5%감소
    //NOTE: update inner join도 가능하지만, meekrodb 기준으로 깔끔하게.
    $unsuppliedCities = $db->query('SELECT city, nation, trust, name FROM city WHERE supply = 0');
    foreach(Util::arrayGroupBy($unsuppliedCities, 'nation') as $nationID => $cityList){
        $cityIDList = Util::squeezeFromArray($cityList, 'city');
        $db->update('general', [
            'crew'=>$db->sqleval('crew*0.95'),
            'atmos'=>$db->sqleval('atmos*0.95'),
            'train'=>$db->sqleval('train*0.95'),
        ], 'city IN %li AND nation = %i', $cityIDList, $nationID);
    }

    //민심30이하 공백지 처리
    $lostCities = [];
    foreach($unsuppliedCities as $unsuppliedCity){
        if($unsuppliedCity['trust'] >= 30){
            continue;
        }
        $lostCities[$unsuppliedCity['city']] = $unsuppliedCity;
    }
    
    if($lostCities){
        foreach($lostCities as $lostCity){
            $josaYi = JosaUtil::pick($lostCity['name'], '이');
            $logger->pushGlobalHistoryLog("<R><b>【고립】</b></><G><b>{$lostCity['name']}</b></>{$josaYi} 보급이 끊겨 <R>미지배</> 도시가 되었습니다.");
        }
        $db->update('general', [
            'officer_level'=>1,
            'officer_city'=>0
        ], 'officer_city IN %li', array_keys($lostCities));
        $db->update('city', [
            'nation'=>0,
            'officer4set'=>0,
            'officer3set'=>0,
            'officer2set'=>0,
            'conflict'=>'{}',
            'term'=>0,
            'front'=>0
        ], 'city IN %li', array_keys($lostCities));
    }
    
    //접률감소, 건국제한-1
    $db->update('general', [
        'connect'=>$db->sqleval('floor(connect*0.99)'),
        'makelimit'=>$db->sqleval('greatest(0, makelimit - 1)'),
    ], true);
    //전략제한-1, 외교제한-1, 세율동기화
    $db->update('nation', [
        'strategic_cmd_limit'=>$db->sqleval('greatest(0, strategic_cmd_limit - 1)'),
        'surlimit'=>$db->sqleval('greatest(0, surlimit - 1)'),
        'rate_tmp'=>$db->sqleval('rate')
    ], true);

    //도시훈사 180년 60, 220년 87, 240년 100
    $rate = Util::round(($admin['year'] - $admin['startyear']) / 1.5) + 60;
    if($rate > 100) $rate = 100;

    $ratio = 100;
    // 20 ~ 140원
    $develcost = ($admin['year'] - $admin['startyear'] + 10) * 2;
    $gameStor->gold_rate = $ratio;
    $gameStor->rice_rate = $ratio;
    $gameStor->city_rate = $rate;
    $gameStor->develcost = $develcost;

    //매달 사망자 수입 결산
    processWarIncome($ratio);

    //계략, 전쟁표시 해제
    $db->update('city', [
        'state'=>$db->sqleval(<<<EOD
CASE
WHEN state=31 THEN 0
WHEN state=32 THEN 31
WHEN state=33 THEN 0
WHEN state=34 THEN 33
WHEN state=41 THEN 0
WHEN state=42 THEN 41
WHEN state=43 THEN 42
ELSE state END
EOD),
        'term'=>$db->sqleval('greatest(0, term - 1'),
        'conflict'=>$db->sqleval('if(term = 0,%s,conflict)', '{}'),
    ], true);

    //첩보-1
    foreach($db->queryAllLists("SELECT nation, spy FROM nation WHERE spy!='' AND spy!='{}'") as [$nationNo, $rawSpy]){
        $spyInfo = Json::decode($rawSpy);

        foreach($spyInfo as $cityNo => $remainMonth){
            if($remainMonth <= 1){
                unset($spyInfo[$cityNo]);
            }
            else{
                $spyInfo[$cityNo] -= 1;
            }
        }

        $db->update('nation', [
            'spy'=>Json::encode($spyInfo, Json::EMPTY_ARRAY_IS_DICT)
        ], 'nation=%i', $nationNo);
    }
    
    return true;
}

// 외교 로그처리, 외교 상태 처리
function postUpdateMonthly() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'scenario']);

    $history = [];

    //도시 수 측정
    $cityNations = [];
    foreach($db->queryAllLists('SELECT city, name, nation FROM city') as [$cityID, $cityName, $cityNation]){
        if(!key_exists($cityNation, $cityNations)){
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
    $nations = $db->query('SELECT
    A.nation,
    A.gennum, A.aux,
    round((
        round(((A.gold+A.rice)+(select sum(gold+rice) from general where nation=A.nation))/100)
        +A.tech
        +if(A.level=0,0,(
            select round(
                sum(pop)*sum(pop+agri+comm+secu+wall+def)/sum(pop_max+agri_max+comm_max+secu_max+wall_max+def_max)/100
            ) from city where nation=A.nation and supply=1
        ))
        +(select sum(leadership+strength+intel) from general where nation=A.nation)
        +(select round(sum(dex1+dex2+dex3+dex4+dex5)/1000) from general where nation=A.nation)
        +(select round(sum(experience+dedication)/100) from general where nation=A.nation)
        +(select round(avg(connect)) from general where nation=A.nation)
    )/10)
    as power,
    (select sum(crew) from general where nation=A.nation) as totalCrew
    from nation A
    group by A.nation');
    foreach($nations as $nation) {
        $genNum[$nation['nation']] = $nation['gennum'];

        $aux = Json::decode($nation['aux']);

        //약간의 랜덤치 부여 (95% ~ 105%)
        
        $nation['power'] = Util::round($nation['power'] * (rand()%101 + 950) / 1000);
        $aux['maxPower'] = max($aux['maxPower']??0, $nation['power']);
        $aux['maxCrew'] = max($aux['maxCrew']??0, Util::toInt($nation['totalCrew']));

        if(count($cityNations[$nation['nation']]??[]) > count($aux['maxCities']??[])){
            $aux['maxCities'] = $cityNations[$nation['nation']];
        }

        $db->update('nation', [
            'power'=>$nation['power'],
            'aux'=>Json::encode($aux)
        ], 'nation=%i', $nation['nation']);
    }

    // 전쟁기한 세팅
    $query = "select me,you,dead,term from diplomacy where state='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);
        $genCount = $genNum[$dip['me']];
        // 25% 참여율일때 두당 10턴에 4000명 소모한다고 계산
        // 4000 / 10 * 0.25 = 100
        $term = floor($dip['dead'] / 100 / $genCount);
        $dip['dead'] -= $term * 100 * $genCount;
        $term = Util::valueFit($dip['term'] + $term, 0, 13);
        
        $db->update('diplomacy', [
            'term' => $term,
            'dead'=> $dip['dead'],
        ], 'me = %i AND you = %i', $dip['me'], $dip['you']);
    }

    //개전국 로그
    $query = "select me,you from diplomacy where state='1' and term<='1' and me<you";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);
        $nation1 = getNationStaticInfo($dip['me']);
        $name1 = $nation1['name'];
        $nation2 = getNationStaticInfo($dip['you']);
        $name2 = $nation2['name'];

        $josaYi = JosaUtil::pick($name2, '이');
        $josaWa = JosaUtil::pick($name1, '와');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【개전】</b></><D><b>$name1</b></>{$josaWa} <D><b>$name2</b></>{$josaYi} <R>전쟁</>을 시작합니다.";
    }
    //휴전국 로그
    $query = "select A.me as me,A.you as you,A.term as term1,B.term as term2 from diplomacy A, diplomacy B where A.me=B.you and A.you=B.me and A.state='0' and A.me<A.you";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);

        //양측 기간 모두 0이 되는 상황이면 휴전
        if($dip['term1'] <= 1 && $dip['term2'] <= 1) {
            $nation1 = getNationStaticInfo($dip['me']);
            $name1 = $nation1['name'];
            $nation2 = getNationStaticInfo($dip['you']);
            $name2 = $nation2['name'];

            $josaWa = JosaUtil::pick($name1, '와');
            $josaYi = JosaUtil::pick($name2, '이');
            $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【휴전】</b></><D><b>$name1</b></>{$josaWa} <D><b>$name2</b></>{$josaYi} <S>휴전</>합니다.";
            //기한 되면 휴전으로
            $query = "update diplomacy set state='2',term='0' where (me='{$dip['me']}' and you='{$dip['you']}') or (me='{$dip['you']}' and you='{$dip['me']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
    //사상자 초기화
    $query = "update diplomacy set dead=0 WHERE state != 0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //외교 기한-1
    $query = "update diplomacy set term=term-1 where term!=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //불가침 끝나면 통상으로
    $query = "update diplomacy set state='2' where state='7' and term='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //선포 끝나면 교전으로
    $query = "update diplomacy set state='0',term='6' where state='1' and term='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //3,4 기간 끝나면 통합
    checkMerge();
    //5,6 기간 끝나면 합병
    checkSurrender();
    //초반이후 방랑군 자동 해체
    if($admin['year'] >= $admin['startyear']+3) {
        checkWander();
    }
    // 작위 업데이트
    updateNationState();
    refreshNationStaticInfo();
    // 천통여부 검사
    checkEmperior();
    //토너먼트 개시
    triggerTournament();
    // 시스템 거래건 등록
    registerAuction();
    //전방설정
    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        SetNationFront($nation['nation']);
    }
}


function checkWander() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year', 'month']);

    $wanderers = $db->queryFirstColumn('SELECT general.`no` FROM general LEFT JOIN nation ON general.nation = nation.nation WHERE nation.`level` = 0 AND general.`officer_level` = 12');

    foreach(General::createGeneralObjListFromDB($wanderers) as $wanderer){
        $wanderCmd = buildGeneralCommandClass('che_해산', $wanderer, $admin);
        if($wanderCmd->isRunnable()){
            $logger = $wanderer->getLogger();
            $logger->pushGeneralActionLog('초반 제한후 방랑군은 자동 해산됩니다.', ActionLogger::PLAIN);
            $wanderCmd->run();
        }
    }

    if($wanderers){
        refreshNationStaticInfo();
    }
}

function checkMerge() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $mylog = [];
    $youlog = [];
    $history = [];

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select * from diplomacy where state='3' and term='0'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($dipresult);

    for($i=0; $i < $dipcount; $i++) {
        $dip = MYDB_fetch_array($dipresult);

        // 아국군주
        $query = "select no,name,nation from general where nation='{$dip['me']}' and officer_level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $me = MYDB_fetch_array($result);
        // 상대군주
        $query = "select no,name,nation,makenation from general where nation='{$dip['you']}' and officer_level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
        // 모국
        $query = "select nation,name,surlimit,tech from nation where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $younation = MYDB_fetch_array($result);
        // 아국
        $query = "select nation,name,gold,rice,surlimit,tech from nation where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $mynation = MYDB_fetch_array($result);
        //양국 NPC수
        $query = "select no from general where nation='{$you['nation']}' and npc>=2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($result);
        //양국 NPC수
        $query = "select no from general where nation='{$me['nation']}' and npc>=2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount2 = MYDB_num_rows($result);

        //TODO: 로그 기록에 대한 쿼리는 한번만 할 수 있다.
        //피항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $josaWa = JosaUtil::pick($mynation['name'], '와');
        $genlog = ["<C>●</><D><b>{$mynation['name']}</b></>{$josaWa} 통합에 성공했습니다."];
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>{$josaWa} <D><b>{$you['makenation']}</b></>로 통합에 성공");
        }
        //항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount2 = MYDB_num_rows($result);
        $josaWa = JosaUtil::pick($younation['name'], '와');
        $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>{$josaWa} 통합에 성공했습니다.";
        for($i=0; $i < $gencount2; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>{$josaWa} <D><b>{$you['makenation']}</b></>로 통합에 성공");
        }

        $josaRo = JosaUtil::pick($you['makenation'], '로');
        $josaYi = JosaUtil::pick($younation['name'], '이');
        $josaWa = JosaUtil::pick($mynation['name'], '와');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통합】</b></><D><b>{$mynation['name']}</b></>{$josaWa} <D><b>{$younation['name']}</b></>{$josaYi} <D><b>{$you['makenation']}</b></>{$josaRo} 통합하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>【혼란】</b></>통합에 반대하는 세력들로 인해 <D><b>{$you['makenation']}</b></>에 혼란이 일고 있습니다.";
        pushNationHistory($younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합");

        $newGenCount = $gencount + $gencount2;
        $newTech = ($younation['tech']*$gencount + $mynation['tech']*$gencount2)/$newGenCount;

        // 국가 백업
        $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $me['nation']);
        $oldNationGenerals = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $me['nation']);
        $oldNation['generals'] = $oldNationGenerals;
        $oldNation['aux'] = Json::decode($oldNation['aux']);

        // 자금 통합, 외교제한 5년, 기술유지
        $db->update('nation', [
            'name'=>$you['makenation'],
            'gold'=>$db->sqleval('gold+%i',$mynation['gold']),
            'rice'=>$db->sqleval('rice+%i',$mynation['rice']),
            'surlimit'=>24,
            'tech'=>$newTech,
            'gennum'=>$newGenCount
        ], 'nation=%i',$younation['nation']);
        //국가 삭제
        $db->insert('ng_old_nations', [
            'server_id'=>UniqueConst::$serverID,
            'nation'=>$me['nation'],
            'data'=>Json::encode($oldNation)
        ]);

        $db->update('general', [
            'nation'=>0,
            'permission'=>'normal',
        ], 'nation=%i AND npc = 5', $me['nation']);

        $query = "delete from nation where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $db->delete('nation_turn', 'nation_id=%i', $me['nation']);
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',conflict='{}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 장수들 일반으로 하고 상대국 소속으로, 수도로 이동
        $query = "update general set belong=1,officer_level=1,officer_city=0,nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 공헌도0.9, 명성0.9
        //TODO:experience General 객체로 이동
        $query = "update general set dedication=dedication*0.9,experience=experience*0.9 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대도 모두 국가 소속 변경
        $query = "update troop set nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 통합국 모든 도시 5% 감소
        $query = "update city set pop=pop*0.95,agri=agri*0.95,comm=comm*0.95,secu=secu*0.95,trust=trust*0.95,def=def*0.95,wall=wall*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 삭제
        $query = "delete from diplomacy where me='{$me['nation']}' or you='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // NPC들 일부 하야 (양국중 큰쪽 장수수의 90~110%만큼)
        $resignCount = 0;
        if($npccount >= $npccount2) {
            $resignCount = Util::round($npccount*(rand()%21+90)/100);
        } else {
            $resignCount = Util::round($npccount2*(rand()%21+90)/100);
        }

        $npcList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND npc>=2 AND npc != 5 ORDER BY rand() LIMIT %i', $you['nation'], $resignCount);
        if($npcList){
            $db->update('general_turn', [
                'action'=>'che_하야',
                'arg'=>null,
                'brief'=>'하야',
            ], 'general_id IN %li AND turn_idx = 0');
        }
        
        pushGenLog($me, $mylog);
        pushGenLog($you, $youlog);
        pushWorldHistory($history, $admin['year'], $admin['month']);

        $mylog = [];
        $youlog = [];
        $history = [];

        refreshNationStaticInfo();
    }
}

function checkSurrender() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select * from diplomacy where state='5' and term='0'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($dipresult);

    for($i=0; $i < $dipcount; $i++) {
        $mylog = [];
        $youlog = [];
        $history = [];

        $dip = MYDB_fetch_array($dipresult);

        // 아국군주
        $query = "select no,name,nation from general where nation='{$dip['me']}' and officer_level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $me = MYDB_fetch_array($result);
        // 상대군주
        $query = "select no,name,nation,makenation from general where nation='{$dip['you']}' and officer_level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
        // 모국
        $query = "select nation,name,surlimit,tech from nation where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $younation = MYDB_fetch_array($result);
        // 아국
        $query = "select nation,name,gold,rice,surlimit,tech from nation where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $mynation = MYDB_fetch_array($result);
        //양국 NPC수
        $query = "select no from general where nation='{$you['nation']}' and npc>=2 and npc != 5";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($result);
        //양국 NPC수
        $query = "select no from general where nation='{$me['nation']}' and npc>=2 and npc != 5";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount2 = MYDB_num_rows($result);

        //피항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog = ["<C>●</><D><b>{$mynation['name']}</b></> 합병에 성공했습니다."];
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></> 합병에 성공");
        }
        $josaRo = JosaUtil::pick($younation['name'], '로');
        //항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount2 = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>{$josaRo} 항복하여 수도로 이동합니다.";
        for($i=0; $i < $gencount2; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>가 <D><b>{$younation['name']}</b></>{$josaRo} 항복");
        }

        $josaYi = JosaUtil::pick($mynation['name'], '이');
        $josaWa = JosaUtil::pick($mynation['name'], '와');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【투항】</b></><D><b>{$mynation['name']}</b></>{$josaYi} <D><b>{$younation['name']}</b></>{$josaRo} 항복하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>【혼란】</b></>통합에 반대하는 세력들로 인해 <D><b>{$younation['name']}</b></>에 혼란이 일고 있습니다.";
        pushNationHistory($younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>{$josaWa} 합병");

        // 국가 백업
        $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $me['nation']);
        $oldNationGenerals = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $me['nation']);
        $oldNation['generals'] = $oldNationGenerals;
        $oldNation['aux'] = Json::decode($oldNation['aux']);

        $newGenCount = $gencount + $gencount2;
        $newTech = ($younation['tech'] * $gencount + $mynation['tech'] * $gencount2) / $newGenCount;
        // 자금 통합, 외교제한 5년, 기술유지
        $db->update('nation', [
            'gold'=>$db->sqleval('gold+%i',$mynation['gold']),
            'rice'=>$db->sqleval('rice+%i',$mynation['rice']),
            'surlimit'=>24,
            'tech'=>$newTech,
            'gennum'=>$newGenCount
        ], 'nation=%i', $younation['nation']);

        //합병 당한국 모든 도시 10%감소
        $query = "update city set pop=pop*0.9,agri=agri*0.9,comm=comm*0.9,secu=secu*0.9,trust=trust*0.9,def=def*0.9,wall=wall*0.9 where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //합병 시도국 모든 도시 5%감소
        $query = "update city set pop=pop*0.95,agri=agri*0.95,comm=comm*0.95,secu=secu*0.95,trust=trust*0.95,def=def*0.95,wall=wall*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //국가 삭제

        $db->insert('ng_old_nations', [
            'server_id'=>UniqueConst::$serverID,
            'nation'=>$me['nation'],
            'data'=>Json::encode($oldNation)
        ]);

        $db->update('general', [
            'nation'=>0,
            'permission'=>'normal',
        ], 'nation=%i AND npc = 5', $me['nation']);

        $query = "delete from nation where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $db->delete('nation_turn', 'nation_id=%i', $me['nation']);
        // 군주가 있는 위치 구함
        $query = "select city from general where nation='{$you['nation']}' and officer_level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($result);
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',conflict='{}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 제의국 모든 장수들 공헌도0.95, 명성0.95
        //TODO: experience를 General로
        $query = "update general set dedication=dedication*0.95,experience=experience*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 장수들 일반으로 하고 상대국 소속으로, 수도로 이동, 공헌도1.1, 명성0.9
        $query = "update general set belong=1,officer_level=1,officer_city=0,nation='{$you['nation']}',city='{$king['city']}',dedication=dedication*1.1,experience=experience*0.9 where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대도 모두 국가 소속 변경
        $query = "update troop set nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 삭제
        $query = "delete from diplomacy where me='{$me['nation']}' or you='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // NPC들 일부 하야 (양국중 큰쪽 장수수의 90~110%만큼)
        $resignCount = 0;
        if($npccount >= $npccount2) {
            $resignCount = Util::round($npccount*(rand()%21+90)/100);
        } else {
            $resignCount = Util::round($npccount2*(rand()%21+90)/100);
        }
        $npcList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND npc>=2 AND npc != 5 ORDER BY rand() LIMIT %i', $you['nation'], $resignCount);
        if($npcList){
            $db->update('general_turn', [
                'action'=>'che_하야',
                'arg'=>null,
                'brief'=>'하야',
            ], 'general_id IN %li AND turn_idx = 0');
        }

        pushGenLog($me, $mylog);
        pushGenLog($you, $youlog);
        pushWorldHistory($history, $admin['year'], $admin['month']);

        refreshNationStaticInfo();
    }
}

function updateNationState() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $history = array();
    $admin = $gameStor->getValues(['year', 'month', 'fiction', 'startyear', 'show_img_level', 'turnterm', 'turntime']);

    $assemblerCnts = [];
    foreach($db->queryAllLists('SELECT nation,count(no) FROM general WHERE npc = 5 GROUP BY nation') as [$nationID, $assemblerCnt]){
        $assemblerCnts[$nationID] = $assemblerCnt;
    };

    foreach($db->query('SELECT nation,name,level,tech FROM nation') as $nation) {
        //TODO: level이 진관수이소중대특 체계를 벗어날 수 있음
        $citycount = $db->queryFirstField('SELECT count(*) FROM city WHERE nation=%i AND level>=4', $nation['nation']);

        if($citycount == 0) {
            $nationlevel = 0;   // 방랑군
        } elseif($citycount == 1) {
            $nationlevel = 1;   // 호족
        } elseif($citycount <= 4) {
            $nationlevel = 2;   // 군벌
        } elseif($citycount <= 7) {
            $nationlevel = 3;   // 주자사
        } elseif($citycount <= 10) {
            $nationlevel = 4;   // 주목
        } elseif($citycount <= 15) {
            $nationlevel = 5;   // 공
        } elseif($citycount <= 20) {
            $nationlevel = 6;   // 왕
        } else {
            $nationlevel = 7;   // 황제
        }

        if ($nationlevel > $nation['level']) {
            $oldLevel = $nation['level'];
            $nation['level'] = $nationlevel;

            switch ($nationlevel) {
                case 7:
                    $josaUl = JosaUtil::pick(getNationLevel($nationlevel), '을');
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>{$josaUl} 자칭하였습니다.";
                    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>{$josaUl} 자칭");
                    break;
                case 6:
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 등극하였습니다.";
                    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 등극");
                    break;
                case 5:
                case 4:
                case 3:
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 임명되었습니다.";
                    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 임명됨");
                    break;
                case 2:
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 독립하여 <Y>".getNationLevel($nationlevel)."</>로 나섰습니다.";
                    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>로 나서다");
                    break;
            }

            $db->update('nation', [
                'level'=>$nation['level']
            ], 'nation=%i', $nation['nation']);

            $turnRows = [];
            foreach(Util::range(getNationChiefLevel($nation['level']), 12) as $chiefLevel){
                foreach(Util::range(GameConst::$maxChiefTurn) as $turnIdx){
                    $turnRows[] = [
                        'nation_id'=>$nation['nation'],
                        'officer_level'=>$chiefLevel,
                        'turn_idx'=>$turnIdx,
                        'action'=>'휴식',
                        'arg'=>null,
                        'brief'=>'휴식'
                    ];
                }
            }
            $db->insertIgnore('nation_turn', $turnRows);
        }

        $assemblerCnt = $assemblerCnts[$nation['nation']]??0;
        $maxAssemblerCnt = [
            1=>0,
            2=>1,
            3=>3,
            4=>4,
            5=>6,
            6=>7,
            7=>9
        ][$nationlevel]??0;

        if($assemblerCnt < $maxAssemblerCnt){
            $lastAssemblerID = $gameStor->assembler_id??0;

            while($assemblerCnt < $maxAssemblerCnt){
                $lastAssemblerID += 1;
                $npcObj = new Scenario\NPC(
                    999, sprintf('부대장%4d',$lastAssemblerID), null, $nation['nation'], null, 
                    10, 10, 10, 1, $admin['year'] - 15, $admin['year'] + 15,  '은둔', '척사'
                );
                $npcObj->npc = 5;
                $npcObj->build($admin);
                $npcID = $npcObj->generalID;
    
                $db->insert('troop', [
                    'troop_leader'=>$npcID,
                    'name'=>$npcObj->realName,
                    'nation'=>$nation['nation'],
                ]);
                $db->update('general', [
                    'troop'=>$npcID
                ], 'no=%i', $npcID);
    
                //TODO: 5턴간 집합턴 입력
                $assemblerCnt += 1;
                $gameStor->assembler_id = $lastAssemblerID;
            }
        }
            
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
}

function checkStatistic() {
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
        'generals'=>[],
        'nations'=>[],
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
        min(power) as minpower, max(power) as maxpower, avg(power) as avgpower from nation where level>0');
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
            'SELECT nation,name,type,power,gennum,gold+rice as goldrice from nation where level>0 order by power desc', 'nation'
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

    foreach($nations as $nationNo=>&$nation) {
        $general = $nationGeneralInfos[$nationNo];
        $city = $nationCityInfos[$nationNo];
        
        $nation['generalInfo'] = $general;
        $nation['cityInfo'] = $city;

        $nationName .= $nation['name'].'('.getNationType($nation['type']).'), ';
        $powerHist .= "{$nation['name']}({$nation['power']}/{$nation['gennum']}/{$city['cnt']}/{$city['pop']}/{$city['pop_max']}/{$nation['goldrice']}/{$general['goldrice']}/{$general['abil']}/{$general['dex']}/{$general['expded']}), ";

        if(!isset($nationHists[$nation['type']])){
            $nationHists[$nation['type']] = 0;
        }
        $nationHists[$nation['type']]++;
    }

    $auxData['nations']['all'] = $nations;

    $nationHist = '';
    foreach(GameConst::$availableNationType as $nationType){
        if(!Util::array_get($nationHists[$nationType])) { $nationHists[$nationType] = '-'; }
        $nationHist .= getNationType($nationType)."({$nationHists[$nationType]}), ";
    }

    $generals = $db->query('SELECT `no`,npc,personal,special,special2,crewtype FROM general');

    $genCount = 0;
    $npcCount = 0;
    $generalCount = count($generals);

    foreach($generals as $general) {
        if(!isset($personalHists[$general['personal']])){
            $personalHists[$general['personal']] = 0;
        }

        if(!isset($specialHists[$general['special']])){
            $specialHists[$general['special']] = 0;
        }

        if(!isset($specialHists2[$general['special2']])){
            $specialHists2[$general['special2']] = 0;
        }

        if($general['npc'] < 2){
            $genCount+=1;
        }
        else{
            $npcCount+=1;
        }

        $personalHists[$general['personal']]++;
        $specialHists[$general['special']]++;
        $specialHists2[$general['special2']]++;
    }

    foreach($db->queryAllLists(
        'SELECT crewtype, count(crewtype) AS cnt FROM general WHERE recent_war != NULL GROUP BY crewtype'
        ) as [$crewtype, $cnt]
    ){
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

    $personalHistStr = join(', ', array_map(function($histPair){
        [$histKey, $cnt] = $histPair;
        return getGenChar($histKey).'('.$cnt.')';
    }, Util::convertDictToArray($personalHists)));

    $specialHistsStr = join(', ', array_map(function($histPair){
        [$histKey, $cnt] = $histPair;
        return getGeneralSpecialDomesticName($histKey).'('.$cnt.')';
    }, Util::convertDictToArray($specialHists)));

    $specialHists2Str = join(', ', array_map(function($histPair){
        [$histKey, $cnt] = $histPair;
        return getGeneralSpecialWarName($histKey).'('.$cnt.')';
    }, Util::convertDictToArray($specialHists2)));

    $specialHistsAllStr = "$specialHistsStr // $specialHists2Str";

    $crewtypeHistsStr = join(', ', array_map(function($histPair){
        [$histKey, $cnt] = $histPair;
        return GameUnitConst::byID($histKey)->getShortName().'('.$cnt.')';
    }, Util::convertDictToArray($crewtypeHists)));
    
    $db->insert('statistic', [
        'year'=>$admin['year'],
        'month'=>$admin['month'],
        'nation_count'=>$nationCount,
        'nation_name'=>$nationName,
        'nation_hist'=>$nationHist,
        'gen_count'=>$generalCountStr,
        'personal_hist'=>$personalHistStr,
        'special_hist'=>$specialHistsAllStr,
        'power_hist'=>$powerHist,
        'crewtype'=>$crewtypeHistsStr,
        'etc'=>$etc,
        'aux'=>Json::encode($auxData)
    ]);

}


function convForOldGeneral(array $general, int $year, int $month){
    return [
        'server_id'=>UniqueConst::$serverID,
        'general_no'=>$general['no'],
        'owner'=>$general['owner'],
        'name'=>$general['name'],
        'last_yearmonth'=>$year*100+$month,
        'turntime'=>$general['turntime'],
        'data'=>Json::encode($general)
    ];
}

function storeOldGeneral(int $no, int $year, int $month){
    $db = DB::db();
    $general = $db->queryFirstRow('SELECT * FROM general WHERE `no` = %i', $no);
    if(!$general){
        return;
    }
    $data = convForOldGeneral($general, $year, $month);
    $db->insertUpdate(
        'ng_old_generals',
        $data,
        $data
    );
}

function storeOldGenerals(int $nation, int $year, int $month){
    $db = DB::db();
    foreach($db->query('SELECT * FROM general WHERE nation = %i',$nation) as $general){
        $data = convForOldGeneral($general, $year, $month);
        $db->insertUpdate(
            'ng_old_generals',
            $data,
            $data
        );
    }
}

function checkEmperior() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['year', 'month', 'isunited']);

    $query = "select nation,name from nation where level>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    if ($count != 1 || $admin['isunited'] != 0) {
       return;
    }

    $nation = MYDB_fetch_array($result);

    $count = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i', $nation['nation']);
    if(!$count){
        return;
    }
    $allcount = $db->queryFirstField('SELECT count(city) FROM city');

    if ($count != $allcount) {
        return;
    }

    checkStatistic();

    $josaYi = JosaUtil::pick($nation['name'], '이');

    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaYi} 전토를 통일");

    $gameStor->isunited = 2;
    $gameStor->conlimit = $gameStor->conlimit*100;

    foreach($db->queryFirstColumn('SELECT no FROM general WHERE npc<2 AND age>=%i', GameConst::$minPushHallAge) as $hallGeneralNo){
        CheckHall($hallGeneralNo);
    }

    $query = "select nation,name,type,color,gold,rice,power,gennum from nation where nation='{$nation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select SUM(pop) as totalpop,SUM(pop_max) as maxpop from city where nation='{$nation['nation']}'"; // 도시 이름 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($cityresult);
    $pop = "{$city['totalpop']} / {$city['maxpop']}";
    $poprate = round($city['totalpop']/$city['maxpop']*100, 2);
    $poprate .= " %";

    $chiefs = Util::convertArrayToDict(
        $db->query(
            'SELECT name,picture,belong,officer_level FROM general WHERE nation=%i AND officer_level >= 5',
            $nation['nation']
        ),
        'officer_level'
    );

    $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $nation['nation']);
    $oldNationGenerals = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $nation['nation']);
    $oldNation['generals'] = $oldNationGenerals;

    $tigers = $db->query('SELECT value, name 
        FROM rank_data LEFT JOIN general ON rank_data.general_id = general.no 
        WHERE rank_data.nation_id = %i AND rank_data.type = "warnum" AND value > 0 ORDER BY value DESC LIMIT 5',
        $nation['nation']
    );// 오호장군

    $tigerstr = join(', ', array_map(function($arr){
        $number = number_format($arr['value']);
        return "{$arr['name']}【{$number}】";
    }, $tigers));

    $eagles = $db->query('SELECT value, name 
        FROM rank_data LEFT JOIN general ON rank_data.general_id = general.no 
        WHERE rank_data.nation_id = %i AND rank_data.type = "firenum" AND value > 0 ORDER BY value DESC LIMIT 7', 
        $nation['nation']
    );// 건안칠자

    $eaglestr = join(', ', array_map(function($arr){
        $number = number_format($arr['value']);
        return "{$arr['name']}【{$number}】";
    }, $eagles));

    $log = ["<C>●</>{$admin['year']}년 {$admin['month']}월: <D><b>{$nation['name']}</b></>{$josaYi} 전토를 통일하였습니다."];

    $query = "select no,name from general where nation='{$nation['nation']}' order by dedication desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    $gen = '';
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        $gen .= "{$general['name']}, ";

        pushGenLog($general, $log);
    }

    $nation['type'] = getNationType($nation['type']);

    $query = "select MAX(nation_count) as nc,MAX(gen_count) as gc from statistic";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $stat = MYDB_fetch_array($result);

    $query = "select count(*) as cnt from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_fetch_array($result);

    $statNC = "1 / {$stat['nc']}";
    $statGC = "{$gencount['cnt']} / {$stat['gc']}";

    $query = "select nation_count,nation_name,nation_hist from statistic where nation_count='{$stat['nc']}' limit 0,1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $statNation = MYDB_fetch_array($result);

    $query = "select gen_count,personal_hist,special_hist,aux from statistic order by no desc limit 0,1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $statGeneral = MYDB_fetch_array($result);

    $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $nation['nation']);
    $oldNation['generals'] = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $nation['nation']);
    $oldNation['aux'] = Json::decode($oldNation['aux']);

    storeOldGenerals(0, $admin['year'], $admin['month']);
    storeOldGenerals($nation['nation'], $admin['year'], $admin['month']);

    $db->insert('ng_old_nations', [
        'server_id'=>UniqueConst::$serverID,
        'nation'=>$nation['nation'],
        'data'=>Json::encode($oldNation)
    ]);

    $noNationGeneral = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=0');
    $db->insert('ng_old_nations', [
        'server_id'=>UniqueConst::$serverID,
        'nation'=>0,
        'data'=>Json::encode([
            'nation'=>0,
            'name'=>'재야',
            'generals'=>$noNationGeneral
        ])
    ]);

    $nationHistory = DB::db()->queryFirstField('SELECT `history` FROM `nation` WHERE `nation` = %i', $nation['nation']);

    $serverCnt = $db->queryFirstField('SELECT count(*) FROM ng_games');
    $serverName = UniqueConst::$serverName;

    $db->update('ng_games', [
        'winner_nation'=>$nation['nation']
    ], 'server_id=%s', UniqueConst::$serverID);

    $db->insert('emperior', [
        'phase'=>$serverName.$serverCnt.'기',
        'server_id'=>UniqueConst::$serverID,
        'nation_count'=>$statNC,
        'nation_name'=>$statNation['nation_name'],
        'nation_hist'=>$statNation['nation_hist'],
        'gen_count'=>$statGC,
        'personal_hist'=>$statGeneral['personal_hist'],
        'special_hist'=>$statGeneral['special_hist'],
        'name'=>$nation['name'],
        'type'=>$nation['type'],
        'color'=>$nation['color'],
        'year'=>$admin['year'],
        'month'=>$admin['month'],
        'power'=>$nation['power'],
        'gennum'=>$nation['gennum'],
        'citynum'=>$allcount,
        'pop'=>$pop,
        'poprate'=>$poprate,
        'gold'=>$nation['gold'],
        'rice'=>$nation['rice'],
        'l12name'=>$chiefs[12]['name'],
        'l12pic'=>$chiefs[12]['picture'],
        'l11name'=>$chiefs[11]['name'],
        'l11pic'=>$chiefs[11]['picture'],
        'l10name'=>$chiefs[10]['name'],
        'l10pic'=>$chiefs[10]['picture'],
        'l9name'=>$chiefs[9]['name'],
        'l9pic'=>$chiefs[9]['picture'],
        'l8name'=>$chiefs[8]['name'],
        'l8pic'=>$chiefs[8]['picture'],
        'l7name'=>$chiefs[7]['name'],
        'l7pic'=>$chiefs[7]['picture'],
        'l6name'=>$chiefs[6]['name'],
        'l6pic'=>$chiefs[6]['picture'],
        'l5name'=>$chiefs[5]['name'],
        'l5pic'=>$chiefs[5]['picture'],
        'tiger'=>$tigerstr,
        'eagle'=>$eaglestr,
        'gen'=>$gen,
        'history'=>$nationHistory,
        'aux'=>$statGeneral['aux']
    ]);

    $history = ["<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통일】</b></><D><b>{$nation['name']}</b></>{$josaYi} 전토를 통일하였습니다."];
    pushWorldHistory($history, $admin['year'], $admin['month']);

    //연감 월결산
    LogHistory();
}
