<?php
namespace sammo;
/**
 * 시간 단위로 일어나는 이벤트들에 대한 함수 모음
 */

 
//1월마다 실행
function processSpring() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    //인구 증가
    popIncrease();
    // 1월엔 무조건 내정 1% 감소
    $query = "update city set dead=0,agri=agri*0.99,comm=comm*0.99,secu=secu*0.99,def=def*0.99,wall=wall*0.99";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 유지비 1%
    $query = "update general set gold=gold*0.99 where gold>1000 and gold<=10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 3%
    $query = "update general set gold=gold*0.97 where gold>10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 유지비 1%
    $query = "update nation set gold=gold*0.99 where gold>1000 and gold<=10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 3%
    $query = "update nation set gold=gold*0.97 where gold>10000 and gold<=100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 5%
    $query = "update nation set gold=gold*0.95 where gold>100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $admin = $gameStor->getValues(['year', 'month']);

    pushWorldHistory(["<R>★</>{$admin['year']}년 {$admin['month']}월: <S>모두들 즐거운 게임 하고 계신가요? ^^ <Y>매너 있는 플레이</> 부탁드리고, <M>지나친 훼접</>은 삼가주세요~</>"], $admin['year'], $admin['month']);
}

function processGoldIncome() {
    $db = DB::db();
    $connect=$db->get();
    $gameStor = new KVStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year','month','gold_rate']);
    $adminLog = [];

    $query = "select name,nation,gold,rate_tmp,bill,type from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    //국가별 처리
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);

        $incomeList = getGoldIncome($nation['nation'], $nation['rate_tmp'], $admin['gold_rate'], $nation['type']);
        $income = $incomeList[0] + $incomeList[1];
        $originoutcome = getGoldOutcome($nation['nation'], 100);    // 100%의 지급량
        $outcome = Util::round($originoutcome * $nation['bill'] / 100);   // 지급량에 따른 요구량
        // 실제 지급량 계산
        $nation['gold'] += $income;
        // 기본량도 안될경우
        if($nation['gold'] < GameConst::$basegold) {
            $realoutcome = 0;
            // 실지급률
            $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
        } elseif($nation['gold'] - GameConst::$basegold < $outcome) {
            $realoutcome = $nation['gold'] - GameConst::$basegold;
            $nation['gold'] = GameConst::$basegold;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        } else {
            $realoutcome = $outcome;
            $nation['gold'] -= $realoutcome;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        }
        $adminLog[] = StringUtil::padStringAlignRight((string)$nation['name'],12," ")
            ." // 세금 : ".StringUtil::padStringAlignRight((string)$income,6," ")
            ." // 세출 : ".StringUtil::padStringAlignRight((string)$originoutcome,6," ")
            ." // 실제 : ".tab2((string)$realoutcome,6," ")
            ." // 지급률 : ".tab2((string)round($ratio*100,2),5," ")
            ." % // 결과금 : ".tab2((string)$nation['gold'],6," ");

        $query = "select no,name,nation from general where nation='{$nation['nation']}' and level>='9'";
        $coreresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $corecount = MYDB_num_rows($coreresult);
        $corelog = ["<C>●</>이번 수입은 금 <C>$income</>입니다."];
        for($j=0; $j < $corecount; $j++) {
            $coregen = MYDB_fetch_array($coreresult);
            pushGenLog($coregen, $corelog);
        }

        $query = "update nation set gold='{$nation['gold']}' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select no,name,nation,dedication,gold from general where nation='{$nation['nation']}' AND npc != 5";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        // 각 장수들에게 지급
        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            $gold = Util::round(getBill($general['dedication'])*$ratio);
            $general['gold'] += $gold;

            $query = "update general set gold='{$general['gold']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($general, ["<C>●</>봉급으로 금 <C>$gold</>을 받았습니다."]);
        }
    }

    pushWorldHistory(["<C>●</>{$admin['year']}년 {$admin['month']}월:<W><b>【지급】</b></>봄이 되어 봉록에 따라 자금이 지급됩니다."], $admin['year'], $admin['month']);
    pushAdminLog($adminLog);
}

function popIncrease() {
    $db = DB::db();
    $connect=$db->get();

    $rate = [];
    $type = [];

    $query = "select nation,rate_tmp,type from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);
        $rate[$nation['nation']] = $nation['rate_tmp'];
        $type[$nation['nation']] = $nation['type'];
    }

    $query = "select * from city where supply='1'"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    // 인구 및 민심
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);

        $pop = $city['pop'];
        if($city['nation'] == 0) {
            $pop = $city['pop'];  // 공백지는 증가하지 않게
            $citytrust = 50;

            $ratio = 0.99;   // 공백지는 수비 빼고 약간씩 감소
            $agri = intval($city['agri'] * $ratio);
            $comm = intval($city['comm'] * $ratio);
            $secu = intval($city['secu'] * $ratio);
            $def  = $city['def'];
            $wall = $city['wall'];
        } else {
            $ratio = (20 - $rate[$city['nation']])/200;  // 20일때 0% 0일때 10% 100일때 -40%
            $agri = $city['agri'] + intval($city['agri'] * $ratio);  //내정도 증감
            $comm = $city['comm'] + intval($city['comm'] * $ratio);
            $secu = $city['secu'] + intval($city['secu'] * $ratio);
            $def  = $city['def']  + intval($city['def']  * $ratio);
            $wall = $city['wall'] + intval($city['wall'] * $ratio);
            $ratio = (30 - $rate[$city['nation']])/200;  // 20일때 5% 5일때 12.5% 50일때 -10%
            if($ratio >= 0) {
                // 국가보정
                if($type[$city['nation']] == 4 || $type[$city['nation']] == 6 || $type[$city['nation']] == 7 || $type[$city['nation']] == 8 || $type[$city['nation']] == 12 || $type[$city['nation']] == 13) { $ratio *= 1.2; }
                if($type[$city['nation']] == 1 || $type[$city['nation']] == 3) { $ratio *= 0.8; }
                $ratio *= (1 + $city['secu']/$city['secu2']/10);    //치안에 따라 최대 10% 추가
            } else {
                // 국가보정
                if($type[$city['nation']] == 4 || $type[$city['nation']] == 6 || $type[$city['nation']] == 7 || $type[$city['nation']] == 8 || $type[$city['nation']] == 12 || $type[$city['nation']] == 13) { $ratio *= 0.8; }
                if($type[$city['nation']] == 1 || $type[$city['nation']] == 3) { $ratio *= 1.2; }
                $ratio *= (1 - $city['secu']/$city['secu2']/10);    //치안에 따라 최대 10% 경감
            }

            $pop = $city['pop'] + (int)($city['pop'] * $ratio) + 5000;  // 기본 5000명은 증가

            $ratio = round($ratio*100, 2);
            $citytrust = $city['trust'];
            $citytrust = $citytrust + (20 - $rate[$city['nation']]);
            $citytrust = Util::valueFit($citytrust, 0, 100);
        }
        if($pop > $city['pop2']) { $pop = $city['pop2']; }
        if($pop < 0) { $pop = 0; }
        if($agri > $city['agri2']) { $agri = $city['agri2']; }
        if($comm > $city['comm2']) { $comm = $city['comm2']; }
        if($secu > $city['secu2']) { $secu = $city['secu2']; }
        if($def > $city['def2']) { $def= $city['def2']; }
        if($wall > $city['wall2']) { $wall = $city['wall2']; }

        //시세
        $query = "update city set pop='$pop',trust='$citytrust',agri='$agri',comm='$comm',secu='$secu',def='$def',wall='$wall' where city='{$city['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function getGoldIncome($nationNo, $rate, $admin_rate, $type) {
    $db = DB::db();
    $connect=$db->get();

    $level2 = [];
    $level3 = [];
    $level4 = [];
    

    $query = "select no,city from general where nation='$nationNo' and level=4"; // 태수
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level4[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=3"; // 군사
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level3[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=2"; // 종사
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level2[$gen['no']] = $gen['city'];
    }

    $nation = getNationStaticInfo($nationNo);

    $query = "select * from city where nation='$nationNo' and supply='1'"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    //총 수입 구함
    $income = [0, 0];  // income[0] : 세수, income[1] : 수비병 세수
    for($j=0; $j < $citycount; $j++) {
        $city = MYDB_fetch_array($cityresult);

        //민충 0~100 : 50~100 수입
        $ratio = $city['trust'] / 2 + 50;
        $tax1 = ($city['pop'] * $city['comm'] / $city['comm2'] * $ratio / 1000) / 3;
        $tax1 *= (1 + $city['secu']/$city['secu2']/10);    //치안에 따라 최대 10% 추가
        //도시 관직 추가 세수
        if(Util::array_get($level4[$city['gen1']]) == $city['city']) { $tax1 *= 1.05;  }
        if(Util::array_get($level3[$city['gen2']]) == $city['city']) { $tax1 *= 1.05;  }
        if(Util::array_get($level2[$city['gen3']]) == $city['city']) { $tax1 *= 1.05;  }
        //수도 추가 세수 130%~105%
        if($city['city'] == $nation['capital']) { $tax1 *= 1+(1/3/$nation['level']); };

        $income[0] += $tax1;
    }
    $income[0] *= ($rate / 20);

    // 국가보정
    if($type == 1)                                              { $income[0] *= 1.1; $income[1] *= 1.1; }
    if($type == 9 || $type == 10 || $type == 11)                { $income[0] *= 0.9; $income[1] *= 0.9; }

    $income[0] = Util::round($income[0] * ($admin_rate/100));
    $income[1] = Util::round($income[1] * ($admin_rate/100));

    return $income;
}

function processDeadIncome($admin_rate) {
    $db = DB::db();
    $connect=$db->get();

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        $income = getDeadIncome($nation['nation'], $nation['type'], $admin_rate);

//  단기수입 금만적용
//        $query = "update nation set gold=gold+'$income',rice=rice+'$income' where nation='{$nation['nation']}'";
        $query = "update nation set gold=gold+'$income' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    // 10%수입, 20%부상병
    $query = "update city set pop=pop+dead*0.2,dead='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function getDeadIncome($nation, $type, $admin_rate) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select dead from city where nation='$nation' and dead>'0' and supply='1'"; // 도시 목록
    $cityResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($cityResult);

    $income = 0;    // 단기수입
    if($cityCount > 0) {
        for($k=0; $k < $cityCount; $k++) {
            $city = MYDB_fetch_array($cityResult);

            $income += $city['dead'];
        }
        $income /= 10;

        // 국가보정
        if($type == 1)                { $income *= 1.1; }
        if($type == 9 || $type == 10) { $income *= 0.9; }

        $income = Util::round($income * $admin_rate / 100);
    }
    return $income;
}

function getGoldOutcome($nation, $bill) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select dedication from general where nation='$nation' AND npc != 5"; // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);

    //총 지출 구함
    $outcome = 0;
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        $outcome += getBill($general['dedication']);
    }

    $outcome = Util::round($outcome * $bill / 100);

    return $outcome;
}

//7월마다 실행
function processFall() {
    $db = DB::db();
    $connect=$db->get();

    //인구 증가
    popIncrease();
    // 7월엔 무조건 내정 1% 감소
    $query = "update city set dead=0,agri=agri*0.99,comm=comm*0.99,secu=secu*0.99,def=def*0.99,wall=wall*0.99";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 유지비 3%
    $query = "update general set rice=rice*0.97 where rice>10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 1%
    $query = "update general set rice=rice*0.99 where rice>1000 and rice<=10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 5%
    $query = "update nation set rice=rice*0.95 where rice>100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 3%
    $query = "update nation set rice=rice*0.97 where rice>10000 and rice<=100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 1%
    $query = "update nation set rice=rice*0.99 where rice>2000 and rice<=10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function processRiceIncome() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['year','month','rice_rate']);
    $adminLog = [];

    $query = "select name,nation,rice,rate_tmp,bill,type from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    //국가별 처리
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);

        $incomeList = getRiceIncome($nation['nation'], $nation['rate_tmp'], $admin['rice_rate'], $nation['type']);
        $income = $incomeList[0] + $incomeList[1];
        $originoutcome = getRiceOutcome($nation['nation'], 100);    // 100%의 지급량
        $outcome = Util::round($originoutcome * $nation['bill'] / 100);   // 지급량에 따른 요구량

        // 실제 지급량 계산
        $nation['rice'] += $income;
        // 기본량도 안될경우
        if($nation['rice'] < GameConst::$baserice) {
            $realoutcome = 0;
            // 실지급률
            $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
        } elseif($nation['rice'] - GameConst::$baserice < $outcome) {
            $realoutcome = $nation['rice'] - GameConst::$baserice;
            $nation['rice'] = GameConst::$baserice;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        } else {
            $realoutcome = $outcome;
            $nation['rice'] -= $realoutcome;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        }
        $adminLog[] = StringUtil::padStringAlignRight($nation['name'],12," ")
            ." // 세곡 : ".StringUtil::padStringAlignRight((string)$income,6," ")
            ." // 세출 : ".StringUtil::padStringAlignRight((string)$originoutcome,6," ")
            ." // 실제 : ".tab2((string)$realoutcome,6," ")
            ." // 지급률 : ".tab2((string)round($ratio*100,2),5," ")
            ." % // 결과곡 : ".tab2((string)$nation['rice'],6," ");

        $query = "select no,name,nation from general where nation='{$nation['nation']}' and level>='9'";
        $coreresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $corecount = MYDB_num_rows($coreresult);
        $corelog = ["<C>●</>이번 수입은 쌀 <C>$income</>입니다."];
        for($j=0; $j < $corecount; $j++) {
            $coregen = MYDB_fetch_array($coreresult);
            pushGenLog($coregen, $corelog);
        }

        $query = "update nation set rice='{$nation['rice']}' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select no,name,nation,dedication,rice from general where nation='{$nation['nation']}' AND npc != 5";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        // 각 장수들에게 지급
        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            $rice = Util::round(getBill($general['dedication'])*$ratio);
            $general['rice'] += $rice;

            $query = "update general set rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($general, ["<C>●</>봉급으로 쌀 <C>$rice</>을 받았습니다."]);
        }
    }

    pushWorldHistory(["<C>●</>{$admin['year']}년 {$admin['month']}월:<W><b>【지급】</b></>가을이 되어 봉록에 따라 군량이 지급됩니다."], $admin['year'], $admin['month']);
    pushAdminLog($adminLog);
}

function getRiceIncome($nationNo, $rate, $admin_rate, $type) {
    $db = DB::db();
    $connect=$db->get();

    $level2 = [];
    $level3 = [];
    $level4 = [];

    $query = "select no,city from general where nation='$nationNo' and level=4"; // 태수
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level4[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=3"; // 군사
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level3[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=2"; // 종사
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level2[$gen['no']] = $gen['city'];
    }

    $nation = getNationStaticInfo($nationNo);

    $query = "select * from city where nation='$nationNo' and supply='1'"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    //총 수입 구함
    $income = [0, 0];  // income[0] : 세수, income[1] : 수비병 세수
    for($j=0; $j < $citycount; $j++) {
        $city = MYDB_fetch_array($cityresult);

        //민충 0~100 : 50~100 수입
        $ratio = $city['trust'] / 2 + 50;
        $tax1 = ($city['pop'] * $city['agri'] / $city['agri2'] * $ratio / 1000) / 3;
        $tax2 = $city['def'] * $city['wall'] / $city['wall2'] / 3;
        $tax1 *= (1 + $city['secu']/$city['secu2']/10);    //치안에 따라 최대 10% 추가
        $tax2 *= (1 + $city['secu']/$city['secu2']/10);    //치안에 따라 최대 10% 추가
        //도시 관직 추가 세수
        if(Util::array_get($level4[$city['gen1']]) == $city['city']) { $tax1 *= 1.05; $tax2 *= 1.05; }
        if(Util::array_get($level3[$city['gen2']]) == $city['city']) { $tax1 *= 1.05; $tax2 *= 1.05; }
        if(Util::array_get($level2[$city['gen3']]) == $city['city']) { $tax1 *= 1.05; $tax2 *= 1.05; }
        //수도 추가 세수 130%~105%
        if($city['city'] == $nation['capital']) { $tax1 *= 1+(1/3/$nation['level']); $tax2 *= 1+(1/3/$nation['level']); }
        $income[0] += $tax1;
        $income[1] += $tax2;
    }
    $income[0] *= ($rate / 20);

    // 국가보정
    if($type == 8)                              { $income[0] *= 1.1; $income[1] *= 1.1; }
    if($type == 2 || $type == 4 || $type == 13) { $income[0] *= 0.9; $income[1] *= 0.9; }

    $income[0] = Util::round($income[0] * ($admin_rate/100));
    $income[1] = Util::round($income[1] * ($admin_rate/100));

    return $income;
}

function getRiceOutcome($nation, $bill) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select dedication from general where nation='$nation' AND npc != 5"; // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);

    //총 지출 구함
    $outcome = 0;
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        $outcome += getBill($general['dedication']);
    }

    $outcome = Util::round($outcome * $bill / 100);

    return $outcome;
}

function tradeRate() {
    $db = DB::db();

    foreach($db->query('SELECT city,level FROM city') as $city){
        //시세
        switch($city['level']) {
        case 1: $per =   0; break;
        case 2: $per =   0; break;
        case 3: $per =   0; break;
        case 4: $per = 0.2; break;
        case 5: $per = 0.4; break;
        case 6: $per = 0.6; break;
        case 7: $per = 0.8; break;
        case 8: $per =   1; break;
        default:$per =   0; break;
        }
        if($per > 0 && Util::randBool($per)) {
            $trade = Util::randRangeInt(95, 105);
        } else {
            $trade = null;
        }
        $db->update('city', [
            'trade'=>$trade
        ], 'city=%i', $city['city']);
    }
}

function disaster() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    //재난표시 초기화
    $query = "update city set state=0 where state<=10";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 초반 3년은 스킵
    if($admin['startyear'] + 3 > $admin['year']) return;
    
    $query = "select city,name,secu,secu2 from city"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    $disastertype = rand() % 4;
    $isgood = 0;
    if($admin['month'] == 4 && $disastertype == 3) { $isgood = 1; }
    if($admin['month'] == 7 && $disastertype == 3) { $isgood = 1; }

    $disastercity = [];
    $disasterratio = [];
    $disastername = [];

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        //호황 발생 도시 선택 ( 기본 2% )
        //재해 발생 도시 선택 ( 기본 6% )
        if($isgood == 1) { $ratio = 2 + Util::round($city['secu']/$city['secu2']*5); }    // 2 ~ 7%
        else { $ratio = 6 - Util::round($city['secu']/$city['secu2']*5); }    // 1 ~ 6%

        if(rand()%100+1 < $ratio) {
            $disastercity[] = $city['city'];
            $disasterratio[] = Util::valueFit($city['secu'] / 0.8 / $city['secu2'], 0, 1);
            $disastername[] = $city['name'];
        }
    }

    $disastername = "<G><b>".join(' ', $disastername)."</b></>";
    $disaster = [];

    //재해 처리
    if(count($disastercity)) {
        $state = 0;
        switch($admin['month']) {
        //봄
        case 1:
            switch($disastertype) {
            case 0:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 역병이 발생하여 도시가 황폐해지고 있습니다.";
                $state = 4;
                break;
            case 1:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 추위가 풀리지 않아 얼어죽는 백성들이 늘어나고 있습니다.";
                $state = 3;
                break;
            case 3:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 황건적이 출현해 도시를 습격하고 있습니다.";
                $state = 9;
                break;
            }
            break;
        //여름
        case 4:
            switch($disastertype) {
            case 0:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 홍수로 인해 피해가 급증하고 있습니다.";
                $state = 7;
                break;
            case 1:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 태풍으로 인해 피해가 속출하고 있습니다.";
                $state = 6;
                break;
            case 3:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【호황】</b></>{$disastername}에 호황으로 도시가 번창하고 있습니다.";
                $state = 2;
                $isGood = 1;
                break;
            }
            break;
        //가을
        case 7:
            switch($disastertype) {
            case 0:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 메뚜기 떼가 발생하여 도시가 황폐해지고 있습니다.";
                $state = 8;
                break;
            case 1:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 흉년이 들어 굶어죽는 백성들이 늘어나고 있습니다.";
                $state = 8;
                break;
            case 3:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【풍작】</b></>{$disastername}에 풍작으로 도시가 번창하고 있습니다.";
                $state = 1;
                $isGood = 1;
                break;
            }
            break;
        //겨울
        case 10:
            switch($disastertype) {
            case 0:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 혹한으로 도시가 황폐해지고 있습니다.";
                $state = 3;
                break;
            case 1:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 눈이 많이 쌓여 도시가 황폐해지고 있습니다.";
                $state = 3;
                break;
            case 3:
                $disaster[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 황건적이 출현해 도시를 습격하고 있습니다.";
                $state = 9;
                break;
            }
            break;
        }
        
        if($isgood == 0) {
            for($i=0; $i < count($disastercity); $i++) {
                $ratio = 15 * $disasterratio[$i];
                $ratio = (80 + $ratio) / 100.0; // 치안률 따라서 80~95%
        
                $query = "update city set state='$state',pop=pop*{$ratio},trust=trust*{$ratio},agri=agri*{$ratio},comm=comm*{$ratio},secu=secu*{$ratio},def=def*{$ratio},wall=wall*{$ratio} where city='$disastercity[$i]'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        
                SabotageInjury($disastercity[$i], 1);
            }
        } else {
            for($i=0; $i < count($disastercity); $i++) {
                $ratio = 4 * $disasterratio[$i];
                $ratio = (101 + $ratio) / 100.0; // 치안률 따라서 101~105%
        
                $city = getCity($disastercity[$i]);
                $city['pop'] *= $ratio;   $city['trust'] *= $ratio;  $city['agri'] *= $ratio;
                $city['comm'] *= $ratio;  $city['secu'] *= $ratio;  $city['def'] *= $ratio;
                $city['wall'] *= $ratio;
        
                if($city['pop'] > $city['pop2']) { $city['pop'] = $city['pop2']; }
                if($city['trust'] > 100) { $city['trust'] = 100; }
                if($city['agri'] > $city['agri2']) { $city['agri'] = $city['agri2']; }
                if($city['comm'] > $city['comm2']) { $city['comm'] = $city['comm2']; }
                if($city['secu'] > $city['secu2']) { $city['secu'] = $city['secu2']; }
                if($city['def'] > $city['def2']) { $city['def'] = $city['def2']; }
                if($city['wall'] > $city['wall2']) { $city['wall'] = $city['wall2']; }
        
                $query = "update city set state='$state',pop='{$city['pop']}',trust='{$city['trust']}',agri='{$city['agri']}',comm='{$city['comm']}',secu='{$city['secu']}',def='{$city['def']}',wall='{$city['wall']}' where city='$disastercity[$i]'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }

    pushWorldHistory($disaster, $admin['year'], $admin['month']);
}
