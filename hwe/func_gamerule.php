<?php
namespace sammo;

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

 /**
 * 게임 내부에 사용하는 유틸리티 함수들을 분리
 */

 
//       0     1     2     3     4     5     6     7
//  0    -, 경작, 상재, 발명                         = 3 지력내정
// 10 축성, 수비, 통찰                               = 3 무력내정
// 20 인덕                                           = 1 통솔내정
// 30 거상, 귀모                                     = 2 공통내정

//TODO: 클래스로 이동
function getSpecial($leader, $power, $intel) {
    throw new \sammo\NotImplementedException();
    //통장
    if($leader*0.9 > $power && $leader*0.9 > $intel) {
        $type = array('che_인덕', 'che_귀모');
        $special = $type[array_rand($type)];
        // 귀모는 50% * 5% = 2.5%
        if($special == 31 && Util::randBool(0.95)) {
            $special = 'che_인덕';
        }
    //무장
    } elseif($power >= $intel) {
        $type = array('che_축성', 'che_수비', 'che_통찰', 'che_귀모');
        $special = $type[array_rand($type)];
        // 귀모는 그중에 25% * 10% = 2.5%
        if($special == 'che_귀모' && Util::randBool(0.9)) {
            $type = array('che_축성', 'che_수비', 'che_통찰');
            $special = $type[array_rand($type)];
        }
    //지장
    } elseif($intel > $power) {
        $type = array('che_상재', 'che_경작', 'che_발명', 'che_귀모');
        $special = $type[array_rand($type)];
        // 거상, 귀모는 그중에 25% * 10% = 2.5%
        if($special == 'che_귀모' && Util::randBool(0.9)) {
            $type = array('che_상재', 'che_경작', 'che_발명');
            $special = $type[array_rand($type)];
        }
    } else {
        //귀모. 다만 이쪽으로 빠지지 않음.
        $special = 'che_귀모';
    }
    return $special;
}

//       0     1     2     3     4     5     6     7
// 40 귀병, 신산, 환술, 집중, 신중, 반계             = 6 지력전투
// 50 보병, 궁병, 기병, 공성                         = 4 무력전투
// 60 돌격, 무쌍, 견고, 위압                         = 4 무장전투
// 70 저격, 필살, 징병, 의술, 격노, 척사             = 6 공통전투

function getSpecial2($leader, $power, $intel, $nodex=1, $dex0=0, $dex10=0, $dex20=0, $dex30=0, $dex40=0) {
    throw new \sammo\NotImplementedException();
    $special2 = 70;
    // 숙련 10,000: 25%, 40,000: 50%, 100,000: 79%, 160,000: 100%
    $dex = sqrt($dex0 + $dex10 + $dex20 + $dex30 + $dex40);
    $dex = Util::round($dex / 4);
    // 숙련 10,000: 75%, 40,000: 50%, 100,000: 21%, 160,000: 0%
    // 그중 20%만
    if($nodex == 0 && rand()%100 < 20 && rand()%100 > $dex) {
        if(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex0) {
            $special2 = 50;
            // 숙련이 아얘 없을시 재분배
            if($dex0 <= 0) {
                if($power >= $intel) {
                    $special2 = 50 + rand()%4;
                } else {
                    $special2 = 40;
                }
            }
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex10) {
            $special2 = 51;
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex20) {
            $special2 = 52;
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex30) {
            $special2 = 40;
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex40) {
            $special2 = 53;
        }
    //무장
    } elseif($power >= $intel) {
        $type = array(60, 61, 62, 63, 70, 71, 72, 73, 74, 75);
        $special2 = $type[rand()%10];
        // 의술은 그중에 10% * 20% = 2%
        if(($special2 == 73) && rand()%100 > 20) {
            $type = array(60, 61, 62, 63, 70, 71, 72, 74, 75);
            $special2 = $type[rand()%9];
        }
    //지장
    } elseif($intel > $power) {
        $type = array(41, 42, 43, 44, 45, 70, 71, 72, 73, 74, 75);
        $special2 = $type[rand()%11];
        // 환술은 그중에 9% * 50% = 4.5%
        if(($special2 == 42) && rand()%100 > 50) {
            $type = array(41, 43, 44, 45, 70, 71, 72, 74, 75);
            $special2 = $type[rand()%9];
        }
        // 의술은 그중에 9% * 20% = 1.8%
        if(($special2 == 73) && rand()%100 > 20) {
            $type = array(41, 42, 43, 44, 45, 70, 71, 72, 74, 75);
            $special2 = $type[rand()%10];
        }
    } else {
        $type = array(70, 71, 72, 73, 74, 75);
        $special2 = $type[rand()%6];
    }
    return $special2;
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
        'SELECT city from city where nation IN 
            (SELECT you from diplomacy where me = %i and state=0)'
        , $nationNo
    ) as $city){
        foreach(CityConst::byID($city)->path as $adjKey=>$adjVal){
            $adj3[$adjKey] = $adjVal;
        }
    };
    foreach($db->queryFirstColumn(
        'SELECT city from city where nation IN 
            (SELECT you from diplomacy where me = %i and state=1 and term<=5)'
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
                $adj[$adjKey] = $adjVal;
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
    $connect=$db->get();

    //천도 제한 해제, 관직 변경 제한 해제
    $query = "update nation set capset='0',l12set='0',l11set='0',l10set='0',l9set='0',l8set='0',l7set='0',l6set='0',l5set='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //관직 변경 제한 해제
    $query = "update city set gen1set='0',gen2set='0',gen3set='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

// 벌점 감소와 건국제한-1 전턴제한-1 외교제한-1, 1달마다 실행, 병사 있는 장수의 군량 감소, 수입비율 조정
function preUpdateMonthly() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    //연감 월결산
    $result = LogHistory();
    $history = array();

    if($result == false) { return false; }

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    //배신 횟수 최대 10회 미만
    $query = "update general set betray=9 where betray>9";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //보급선 체크
    checkSupply();
    //미보급도시 10% 감소
    $query = "update city set pop=pop*0.9,trust=trust*0.9,agri=agri*0.9,comm=comm*0.9,secu=secu*0.9,def=def*0.9,wall=wall*0.9 where supply='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //미보급도시 장수 5% 감소
    $query = "select city,nation from city where supply='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    for($i=0; $i < $cityCount; $i++) {
        $city = MYDB_fetch_array($result);
        //병 훈 사 5%감소
        $query = "update general set crew=crew*0.95,atmos=atmos*0.95,train=train*0.95 where city='{$city['city']}' and nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    //민심30이하 공백지 처리
    $query = "select city,name,gen1,gen2,gen3 from city where trust<=30 and supply='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    for($i=0; $i < $cityCount; $i++) {
        $city = MYDB_fetch_array($result);

        $query = "update general set level=1 where no='{$city['gen1']}' or no='{$city['gen2']}' or no='{$city['gen3']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $josaYi = JosaUtil::pick($city['name'], '이');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【고립】</b></><G><b>{$city['name']}</b></>{$josaYi} 보급이 끊겨 <R>미지배</> 도시가 되었습니다.";
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
    //민심30이하 공백지 처리
    $query = "update city set nation='0',gen1='0',gen2='0',gen3='0',conflict='{}',term=0,front=0 where trust<=30 and supply='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //접률감소
    $query = "update general set connect=floor(connect*0.99)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //건국제한, 전략제한, 외교제한-1
    $query = "update general set makelimit=makelimit-1 where makelimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update nation set strategic_cmd_limit=strategic_cmd_limit-1 where strategic_cmd_limit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update nation set surlimit=surlimit-1 where surlimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //세율 동기화 목적
    $query = "update nation set rate_tmp=rate";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

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
    processDeadIncome($ratio);

    //계략, 전쟁표시 해제
    $query = "update city set state=0 where state=31 or state=33";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=state-1 where state=32 or state=34";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set term=term-1 where term>0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set conflict='{}' where term=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=0 where state=41";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=41 where state=42";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=42 where state=43";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 계급 검사 및 승,강급
    $query = "select no,name,dedication,dedlevel,experience,explevel from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);

        $log = [];
        $log = checkDedication($general, $log);
        $log = checkExperience($general, $log);
        pushGenLog($general, $log);
    }

    //첩보-1
    foreach($db->queryAllLists("SELECT nation, spy FROM nation WHERE spy!='' AND spy!='{}'") as [$nationNo, $rawSpy]){
        if (strpos($rawSpy, '|') !== false || is_numeric($rawSpy)) {
           //TODO: 0.8 버전 이후에는 삭제할 것. 이후 버전은 json으로 변경됨.
           $spyInfo = [];
           foreach(explode('|', $rawSpy) as $value){
               $value = intval($value);
               $cityNo = intdiv($value, 10);
               $remainMonth = $value % 10;
               $spyInfo[$cityNo] = $remainMonth;
           }
        }
        else{
            $spyInfo = Json::decode($rawSpy);
        }

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
                sum(pop)*sum(pop+agri+comm+secu+wall+def)/sum(pop2+agri2+comm2+secu2+wall2+def2)/100
            ) from city where nation=A.nation and supply=1
        ))
        +(select sum(leader+power+intel) from general where nation=A.nation)
        +(select round(sum(dex0+dex10+dex20+dex30+dex40)/1000) from general where nation=A.nation)
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
    $connect=$db->get();

    $admin = $gameStor->getValues(['year', 'month']);

    $needRefresh = false;

    // 국가정보, 장수수
    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] != 0){
            continue;
        }

        $needRefresh = true;

        $query = "select no,name,nation,level,turntime from general where nation='{$nation['nation']}' and level=12";
        $kingResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($kingResult);

        pushGenLog($king, ["<C>●</>초반 제한후 방랑군은 자동 해산됩니다."]);
        process_56($king);
    }

    if($needRefresh){
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
        $query = "select no,name,nation from general where nation='{$dip['me']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $me = MYDB_fetch_array($result);
        // 상대군주
        $query = "select no,name,nation,makenation from general where nation='{$dip['you']}' and level='12'";
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
        $query = "update city set nation='{$you['nation']}',gen1='0',gen2='0',gen3='0',conflict='{}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 장수들 일반으로 하고 상대국 소속으로, 수도로 이동
        $query = "update general set belong=1,level=1,nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 공헌도0.9, 명성0.9
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
                'arg'=>null
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
        $query = "select no,name,nation from general where nation='{$dip['me']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $me = MYDB_fetch_array($result);
        // 상대군주
        $query = "select no,name,nation,makenation from general where nation='{$dip['you']}' and level='12'";
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
        $query = "select city from general where nation='{$you['nation']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($result);
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',gen1='0',gen2='0',gen3='0',conflict='{}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 제의국 모든 장수들 공헌도0.95, 명성0.95
        $query = "update general set dedication=dedication*0.95,experience=experience*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 장수들 일반으로 하고 상대국 소속으로, 수도로 이동, 공헌도1.1, 명성0.9
        $query = "update general set belong=1,level=1,nation='{$you['nation']}',city='{$king['city']}',dedication=dedication*1.1,experience=experience*0.9 where nation='{$me['nation']}'";
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
                'arg'=>null
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
    $connect=$db->get();

    $history = array();
    $admin = $gameStor->getValues(['year', 'month', 'fiction', 'startyear', 'show_img_level', 'turnterm']);

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

        if($nationlevel > $nation['level']) {
            $oldLevel = $nation['level'];
            $nation['level'] = $nationlevel;

            switch($nationlevel) {
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

            
            $lastAssemblerID = $gameStor->assembler_id??0;
            for($levelGen = max(1, $oldLevel) + 1; $levelGen <= $nationlevel; $levelGen+=1){
                if(in_array($levelGen, [3, 5, 7])){
                    $genStep = 2;
                }
                else{
                    $genStep = 1;
                }
                
                while($genStep > 0){
                    $lastAssemblerID += 1;
                    $npcObj = new Scenario\NPC(
                        999, '부대장'.$lastAssemblerID, null, $nation['nation'], null, 
                        10, 10, 10, 1, $admin['year'] - 15, $admin['year'] + 15,  '은둔', '척사'
                    );
                    $npcObj->npc = 5;
                    $npcObj->build($admin);
                    $npcID = $npcObj->generalID;

                    $db->insert('troop', [
                        'name'=>$npcObj->realName,
                        'nation'=>$nation['nation'],
                        'no'=>$npcID
                    ]);
                    $troopID = $db->insertId();

                    //TODO: 5턴간 집합턴 입력
                    $genStep -= 1;
                }
            }
            $gameStor->assembler_id = $lastAssemblerID;

            $turnRows = [];
            foreach(range(getNationChiefLevel($oldLevel) - 1, getNationChiefLevel($nation['level']), -1) as $chiefLevel){
                foreach(range(0, GameConst::$maxChiefTurn - 1) as $turnIdx){
                    $turnRows[] = [
                        'nation_id'=>$nation['nation'],
                        'level'=>$chiefLevel,
                        'turn_idx'=>$turnIdx,
                        'action'=>'휴식',
                        'arg'=>null,
                    ];
                }
            }
            $db->insertIgnore('nation_turn', $turnRows);

            $db->update('nation', [
                'level'=>$nation['level']
            ], 'nation=%i', $nation['nation']);
            
            refreshNationStaticInfo();
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
        'SELECT avg(gold) as avggold, avg(rice) as avgrice, avg(dex0+dex10+dex20+dex30) as avgdex, 
        max(dex0+dex10+dex20+dex30) as maxdex, avg(experience+dedication) as avgexpded, max(experience+dedication) as maxexpded
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
            'SELECT nation, sum(leader+power+intel) as abil,sum(gold+rice) as goldrice,
            sum(dex0+dex10+dex20+dex30) as dex,sum(experience+dedication) as expded
            from general GROUP BY nation'
        ),
        'nation'
    );

    $nationCityInfos = Util::convertArrayToDict(
        $db->query('SELECT nation, count(*) as cnt, sum(pop) as pop,sum(pop2) as pop2 from city GROUP BY nation'),
        'nation'
    );

    foreach($nations as $nationNo=>&$nation) {
        $general = $nationGeneralInfos[$nationNo];
        $city = $nationCityInfos[$nationNo];
        
        $nation['generalInfo'] = $general;
        $nation['cityInfo'] = $city;

        $nationName .= $nation['name'].'('.getNationType($nation['type']).'), ';
        $powerHist .= "{$nation['name']}({$nation['power']}/{$nation['gennum']}/{$city['cnt']}/{$city['pop']}/{$city['pop2']}/{$nation['goldrice']}/{$general['goldrice']}/{$general['abil']}/{$general['dex']}/{$general['expded']}), ";

        if(!isset($nationHists[$nation['type']])){
            $nationHists[$nation['type']] = 0;
        }
        $nationHists[$nation['type']]++;
    }

    $auxData['nations']['all'] = $nations;

    $nationHist = '';
    for($i=1; $i <= 13; $i++) {
        if(!Util::array_get($nationHists[$i])) { $nationHists[$i] = '-'; }
        $nationHist .= getNationType($i)."({$nationHists[$i]}), ";
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
        'SELECT crewtype, count(crewtype) AS cnt FROM general WHERE crew>=100 OR deathnum>0 GROUP BY crewtype'
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
        return getGenSpecial($histKey).'('.$cnt.')';
    }, Util::convertDictToArray($specialHists)));

    $specialHists2Str = join(', ', array_map(function($histPair){
        [$histKey, $cnt] = $histPair;
        return getGenSpecial($histKey).'('.$cnt.')';
    }, Util::convertDictToArray($specialHists2)));

    $specialHistsAllStr = "$specialHistsStr // $specialHists2Str";

    $crewtypeHistsStr = join(', ', array_map(function($histPair){
        [$histKey, $cnt] = $histPair;
        return getGenSpecial($histKey).'('.$cnt.')';
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

    $query = "select SUM(pop) as totalpop,SUM(pop2) as maxpop from city where nation='{$nation['nation']}'"; // 도시 이름 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($cityresult);
    $pop = "{$city['totalpop']} / {$city['maxpop']}";
    $poprate = round($city['totalpop']/$city['maxpop']*100, 2);
    $poprate .= " %";

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='12'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level12 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='11'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level11 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='10'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level10 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='9'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level9 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='8'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level8 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='7'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level7 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='6'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level6 = MYDB_fetch_array($genresult);

    $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='5'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level5 = MYDB_fetch_array($genresult);

    $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $nation['nation']);
    $oldNationGenerals = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $nation['nation']);
    $oldNation['generals'] = $oldNationGenerals;

    $query = "select name,picture,killnum from general where nation='{$nation['nation']}' order by killnum desc limit 5";   // 오호장군
    $tigerresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $tigernum = MYDB_num_rows($tigerresult);
    $tigerstr = '';
    for($i=0; $i < $tigernum; $i++) {
        $tiger = MYDB_fetch_array($tigerresult);
        if($tiger['killnum'] > 0) {
            $tigerstr .= "{$tiger['name']}【{$tiger['killnum']}】, ";
        }
    }

    $query = "select name,picture,firenum from general where nation='{$nation['nation']}' order by firenum desc limit 7";   // 건안칠자
    $eagleresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $eaglenum = MYDB_num_rows($eagleresult);
    $eaglestr = '';
    for($i=0; $i < $eaglenum; $i++) {
        $eagle = MYDB_fetch_array($eagleresult);
        if($eagle['firenum'] > 0) {
            $eaglestr .= "{$eagle['name']}【{$eagle['firenum']}】, ";
        }
    }

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
        'l12name'=>$level12['name'],
        'l12pic'=>$level12['picture'],
        'l11name'=>$level11['name'],
        'l11pic'=>$level11['picture'],
        'l10name'=>$level10['name'],
        'l10pic'=>$level10['picture'],
        'l9name'=>$level9['name'],
        'l9pic'=>$level9['picture'],
        'l8name'=>$level8['name'],
        'l8pic'=>$level8['picture'],
        'l7name'=>$level7['name'],
        'l7pic'=>$level7['picture'],
        'l6name'=>$level6['name'],
        'l6pic'=>$level6['picture'],
        'l5name'=>$level5['name'],
        'l5pic'=>$level5['picture'],
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
