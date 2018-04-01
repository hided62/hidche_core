<?php
namespace sammo;

/**
 * 게임 룰에 해당하는 함수 모음
 */

// 37.5 ~ 75
function abilityRand() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 1.0;
    $power  = (rand()%100 + 1) / 100.0 + 1.0;
    $intel  = (rand()%100 + 1) / 100.0 + 1.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

// 14 ~ 75
function abilityLeadpow() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 6.0;
    $power  = (rand()%100 + 1) / 100.0 + 6.0;
    $intel  = (rand()%100 + 1) / 100.0 + 1.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

function abilityLeadint() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 6.0;
    $power  = (rand()%100 + 1) / 100.0 + 1.0;
    $intel  = (rand()%100 + 1) / 100.0 + 6.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

function abilityPowint() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 1.0;
    $power  = (rand()%100 + 1) / 100.0 + 6.0;
    $intel  = (rand()%100 + 1) / 100.0 + 6.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

/**
 * 게임 내부에 사용하는 유틸리티 함수들을 분리
 */

 
//       0     1     2     3     4     5     6     7
//  0    -, 경작, 상재, 발명                         = 3 지력내정
// 10 축성, 수비, 통찰                               = 3 무력내정
// 20 인덕                                           = 1 통솔내정
// 30 거상, 귀모                                     = 2 공통내정

function getSpecial($connect, $leader, $power, $intel) {
    //통장
    if($leader*0.9 > $power && $leader*0.9 > $intel) {
        $type = array(20, 31);
        $special = $type[array_rand($type)];
        // 귀모는 50% * 5% = 2.5%
        if($special == 31 && randBool(0.95)) {
            $special = 20;
        }
    //무장
    } elseif($power >= $intel) {
        $type = array(10, 11, 12, 31);
        $special = $type[array_rand($type)];
        // 귀모는 그중에 25% * 10% = 2.5%
        if($special == 31 && randBool(0.9)) {
            $type = array(10, 11, 12);
            $special = $type[array_rand($type)];
        }
    //지장
    } elseif($intel > $power) {
        $type = array(1, 2, 3, 31);
        $special = $type[array_rand($type)];
        // 거상, 귀모는 그중에 25% * 10% = 2.5%
        if($special == 31 && randBool(0.9)) {
            $type = array(1, 2, 3);
            $special = $type[array_rand($type)];
        }
    } else {
        //귀모. 다만 이쪽으로 빠지지 않음.
        $type = 31;
    }
    return $special;
}

//       0     1     2     3     4     5     6     7
// 40 귀병, 신산, 환술, 집중, 신중, 반계             = 6 지력전투
// 50 보병, 궁병, 기병, 공성                         = 4 무력전투
// 60 돌격, 무쌍, 견고, 위압                         = 4 무장전투
// 70 저격, 필살, 징병, 의술, 격노, 척사             = 6 공통전투

function getSpecial2($connect, $leader, $power, $intel, $nodex=1, $dex0=0, $dex10=0, $dex20=0, $dex30=0, $dex40=0) {
    $special2 = 70;
    // 숙련 10,000: 25%, 40,000: 50%, 100,000: 79%, 160,000: 100%
    $dex = sqrt($dex0 + $dex10 + $dex20 + $dex30 + $dex40);
    $dex = round($dex / 4);
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
    $type = floor($type / 10) * 10;
    return $general["dex{$type}"];
}

function addGenDex($connect, $no, $type, $exp) {
    $type = floor($type / 10) * 10;
    $dexType = "dex{$type}";
    if($type == 30) { $exp = round($exp * 0.90); }     //귀병은 90%효율
    elseif($type == 40) { $exp = round($exp * 0.90); } //차병은 90%효율

    $query = "update general set {$dexType}={$dexType}+{$exp} where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}


//한국가의 전체 전방 설정
function SetNationFront($nationNo) {
    if(!$nationNo) { return; }
    // 도시소유 국가와 선포,교전중인 국가
    
    $adj = [];

    $db = DB::db();
    $enemyCities = $db->queryFirstColumn(
        'SELECT city from city where nation IN 
            (SELECT you from diplomacy where me = %i and (state=0 or (state=1 and term<=3)))'
        , $nationNo
    );
    if($enemyCities) {
        foreach($enemyCities as $city){
            $adj = array_merge($adj, CityConst::byID($city)->path);
        }
    } else {
        //평시이면 공백지
        //NOTE: if, else일 경우 NPC는 전쟁시에는 공백지로 출병하지 않는다는 뜻이 된다.
        foreach ($db->queryFirstColumn('SELECT city,path from city where nation=0') as $city) {
            $adj = array_merge($adj, CityConst::byID($city)->path);
        }
    }

    $db->update('city', [
        'front'=>0
    ], 'nation=%i', $nationNo);

    if($adj){
        $db->update('city', [
            'front'=>0
        ], 'nation=%i and city in %li', $nationNo, array_keys($adj));
    }
}

function checkSupply($connect) {
    include_once("queue.php");

    $query = "select city,nation,path from city";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityNum = MYDB_num_rows($result);
    for($i=0; $i < $cityNum; $i++) {
        $city = MYDB_fetch_array($result);
        $cityPath[$city['city']] = $city['path'];
        $cityNation[$city['city']] = $city['nation'];
        $label[$city['city']] = 0;
    }

    $select = 0;
    $queue = new Queue(20);
    $queue2 = new Queue(20);
    $labelling = 0;
    $marked = 0;
    $comCount = array();

    //모든 도시 마크할 때까지
    while($marked < $cityNum) {
        $queue->clear();    $queue2->clear();
        $q = $queue;        $q2 = $queue2;

        $labelling++;
        //마크 되지 않은 도시부터 라벨링 시작
        for($i=1; $i <= $cityNum; $i++) {
            if($label[$i] == 0) {
                $label[$i] = $labelling;
                $labelMapping[$labelling] = $cityNation[$i];
                isset($comCount[$cityNation[$i]]) ? $comCount[$cityNation[$i]]++ : $comCount[$cityNation[$i]] = 1;
                $q->push($i);
                $marked++;
                break;
            }
        }

        while($q->getSize() > 0 || $q2->getSize() > 0) {
            while($q->getSize() > 0) {
                $city = $q->pop();
                unset($path);
                $path = explode("|", $cityPath[$city]);
                for($i=0; $i < count($path); $i++) {
                    if($label[$path[$i]] == 0 && $cityNation[$path[$i]] == $cityNation[$city]) {
                        $label[$path[$i]] = $labelling;
                        $q2->push($path[$i]);
                        $marked++;
                    }
                }
            }
            if($select == 0) {
                $q2 = $queue;
                $q = $queue2;
            } else {
                $q = $queue;
                $q2 = $queue2;
            }
            $select = 1 - $select;
        }
    }

    //공백지는 다 보급상태
    $query = "update city set supply='1' where nation='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //우선 다 미보급 상태로
    $query = "update city set supply='0' where nation!='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //도시 있는 국가들

    $str = "city='0'";

    //TODO: in 을 쓰는게 낫다
    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }

        $lbl = $label[$nation['capital']];

        for ($k=1; $k <= $cityNum; $k++) {
            if ($lbl == $label[$k]) {
                $str .= " or city='{$k}'";
            }
        }
    }

    $query = "update city set supply='1' where {$str}";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}


function updateYearly($connect) {
    //통계
    checkStatistic($connect);
}

//관직 변경 해제
function updateQuaterly($connect) {
    //천도 제한 해제, 관직 변경 제한 해제
    $query = "update nation set capset='0',l12set='0',l11set='0',l10set='0',l9set='0',l8set='0',l7set='0',l6set='0',l5set='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //관직 변경 제한 해제
    $query = "update city set gen1set='0',gen2set='0',gen3set='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

// 벌점 감소와 건국제한-1 전턴제한-1 외교제한-1, 1달마다 실행, 병사 있는 장수의 군량 감소, 수입비율 조정
function preUpdateMonthly($connect) {
    //연감 월결산
    $result = LogHistory();
    $history = array();

    if($result == false) { return false; }

    $query = "select startyear,year,month,normgeneral from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //배신 횟수 최대 10회 미만
    $query = "update general set betray=9 where betray>9";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //보급선 체크
    checkSupply($connect);
    //미보급도시 10% 감소
    $query = "update city set pop=pop*0.9,rate=rate*0.9,agri=agri*0.9,comm=comm*0.9,secu=secu*0.9,def=def*0.9,wall=wall*0.9 where supply='0'";
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
    $query = "select city,name,gen1,gen2,gen3 from city where rate<='30' and supply='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    for($i=0; $i < $cityCount; $i++) {
        $city = MYDB_fetch_array($result);

        $query = "update general set level=1 where no='{$city['gen1']}' or no='{$city['gen2']}' or no='{$city['gen3']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【고립】</b></><G><b>{$city['name']}</b></>(이)가 보급이 끊겨 <R>미지배</> 도시가 되었습니다.";
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
    //민심30이하 공백지 처리
    $query = "update city set nation='0',gen1='0',gen2='0',gen3='0',conflict='',conflict2='',term=0,front=0 where rate<='30' and supply='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 우선 병사수/100 만큼 소비
    $query = "update general set rice=rice-round(crew/100) where crew>=100";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 쌀이 마이너스인 장수들 소집해제
    $query = "select no,name,rice,crew,city from general where rice<0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);

        // 주민으로 돌아감
        $query = "update city set pop=pop+'{$general['crew']}' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set crew=0,rice=0 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[0] = "<C>●</>군량이 모자라 병사들이 <R>소집해제</>되었습니다!";
        pushGenLog($general, $log);
    }

    //접률감소
    $query = "update general set connect=floor(connect*0.99)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //건국제한, 전략제한, 외교제한-1
    $query = "update general set makelimit=makelimit-1 where makelimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update nation set tricklimit=tricklimit-1 where tricklimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update nation set surlimit=surlimit-1 where surlimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //세율 동기화 목적
    $query = "update nation set rate_tmp=rate";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //도시훈사 180년 60, 220년 87, 240년 100
    $rate = round(($admin['year'] - $admin['startyear']) / 1.5) + 60;
    if($rate > 100) $rate = 100;

    //금률 쌀률, 내정비용
//    $query = "select count(*) as cnt from general";
//    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//    $gencount = MYDB_fetch_array($result);    // 전체 등록자 수
//    $ratio = 50 + round($gencount['cnt'] / $admin['normgeneral'] * 100 / 2); // 300명 등록시에 100% 지급
    $ratio = 100;
    // 20 ~ 140원
    $develcost = ($admin['year'] - $admin['startyear'] + 10) * 2;
    $query = "update game set gold_rate='$ratio',rice_rate='$ratio',city_rate='$rate',develcost='$develcost'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //매달 사망자 수입 결산
    processDeadIncome($connect, $ratio);

    //계략, 전쟁표시 해제
    $query = "update city set state=0 where state=31 or state=33";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=state-1 where state=32 or state=34";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set term=term-1 where term>0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set conflict='',conflict2='' where term=0";
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

        unset($log);
        $log = array();
        $log = checkDedication($connect, $general, $log);
        $log = checkExperience($connect, $general, $log);
        pushGenLog($general, $log);
    }

    //첩보-1
    $query = "select nation,spy from nation where spy!=''";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);
    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);
        $spy = "";  $k = 0; unset($citys);
        if($nation['spy'] != "") { $citys = explode("|", $nation['spy']); }
        while(count($citys)) {
            $citys[$k]--;
            if($citys[$k]%10 != 0) { $spy .= "$citys[$k]"; }
            $k++;
            if($k >= count($citys)) { break; }
            if($citys[$k-1]%10 != 0) { $spy .= "|"; }
        }
        $query = "update nation set spy='$spy' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    
    return true;
}

// 외교 로그처리, 외교 상태 처리
function postUpdateMonthly($connect) {
    $query = "select startyear,year,month,scenario from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

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
    $query = "
select
A.nation,
A.gennum, A.gennum2, A.chemi,
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
as power
from nation A
group by A.nation
";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);
    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);
        $genNum[$nation['nation']] = $nation['gennum'];

        if($nation['gennum'] > $nation['gennum2']) {
            // 장수가 증가했을때
            $nation['chemi'] -= ceil(($nation['gennum'] - $nation['gennum2']) / $nation['gennum'] * 100);
        } else {
            // 장수가 감소했을때
            $nation['chemi'] -= ceil(($nation['gennum2'] - $nation['gennum']) / $nation['gennum2'] * 100);
        }
        // 매달 2씩 증가
        $nation['chemi'] += 2;
        if($nation['chemi'] < 0) { $nation['chemi'] = 0; }
        if($nation['chemi'] > 100) { $nation['chemi'] = 100; }

        //약간의 랜덤치 부여 (95% ~ 105%)
        $nation['power'] = round($nation['power'] * (rand()%101 + 950) / 1000);
        $query = "update nation set power='{$nation['power']}',gennum2='{$nation['gennum']}',chemi='{$nation['chemi']}' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
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
        $term = round($dip['dead'] / 100 / $genCount) + 1;
        if($dip['term'] > $term) { $term = $dip['term']; }
        if($term > 13) { $term = 13; }
        $query = "update diplomacy set term='{$term}' where (me='{$dip['me']}' and you='{$dip['you']}')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
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
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【개전】</b></><D><b>$name1</b></>(와)과 <D><b>$name2</b></>(이)가 <R>전쟁</>을 시작합니다.";
    }
    //휴전국 로그
    $query = "select A.me as me,A.you as you,A.term as term1,B.term as term2 from diplomacy A, diplomacy B where A.me=B.you and A.you=B.me and A.state='0' and A.me<A.you";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    $history = array();
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);

        //양측 기간 모두 0이 되는 상황이면 휴전
        if($dip['term1'] <= 1 && $dip['term2'] <= 1) {
            $nation1 = getNationStaticInfo($dip['me']);
            $name1 = $nation1['name'];
            $nation2 = getNationStaticInfo($dip['you']);
            $name2 = $nation2['name'];
            $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【휴전】</b></><D><b>$name1</b></>(와)과 <D><b>$name2</b></>(이)가 <S>휴전</>합니다.";
            //기한 되면 휴전으로
            $query = "update diplomacy set state='2',term='0' where (me='{$dip['me']}' and you='{$dip['you']}') or (me='{$dip['you']}' and you='{$dip['me']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
    //사상자 초기화
    $query = "update diplomacy set dead=0";
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
    checkMerge($connect);
    //5,6 기간 끝나면 합병
    checkSurrender($connect);
    //초반이후 방랑군 자동 해체
    if($admin['year'] >= $admin['startyear']+3) {
        checkWander($connect);
    }
    // 작위 업데이트
    updateNationState($connect);
    // 천통여부 검사
    checkEmperior($connect);
    //토너먼트 개시
    triggerTournament($connect);
    // 시스템 거래건 등록
    registerAuction($connect);
    //전방설정
    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        SetNationFront($nation['nation']);
    }
}


function checkWander($connect) {
    $query = "select year,month from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

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

        $log[0] = "<C>●</>초반 제한후 방랑군은 자동 해산됩니다.";
        pushGenLog($king, $log);
        process_56($connect, $king);
    }

    if($needRefresh){
        refreshNationStaticInfo();
    }
}

function checkMerge($connect) {
    $query = "select year,month from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

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
        $query = "select nation,name,surlimit,totaltech from nation where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $younation = MYDB_fetch_array($result);
        // 아국
        $query = "select nation,name,gold,rice,surlimit,totaltech from nation where nation='{$me['nation']}'";
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
        $genlog[0] = "<C>●</><D><b>{$mynation['name']}</b></>(와)과 통합에 성공했습니다.";
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합에 성공");
        }
        //항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount2 = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>(와)과 통합에 성공했습니다.";
        for($i=0; $i < $gencount2; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합에 성공");
        }

        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통합】</b></><D><b>{$mynation['name']}</b></>(와)과 <D><b>{$younation['name']}</b></>(이)가 <D><b>{$you['makenation']}</b></>(으)로 통합하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>【혼란】</b></>통합에 반대하는 세력들로 인해 <D><b>{$you['makenation']}</b></>에 혼란이 일고 있습니다.";
        pushNationHistory($younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합");

        $newGenCount = $gencount + $gencount2;
        if($newGenCount < 10) { $newGenCount = 10; }
        $newTotalTech = $younation['totaltech'] + $mynation['totaltech'];
        $newTech = round($newTotalTech / $newGenCount);
        // 자금 통합, 외교제한 5년, 기술유지
        $query = "update nation set name='{$you['makenation']}',gold=gold+'{$mynation['gold']}',rice=rice+'{$mynation['rice']}',surlimit='24',totaltech='$newTotalTech',tech='$newTech',gennum='{$newGenCount}' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //국가 삭제
        $query = "delete from nation where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',gen1='0',gen2='0',gen3='0',conflict='',conflict2='' where nation='{$me['nation']}'";
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
        $query = "update city set pop=pop*0.95,agri=agri*0.95,comm=comm*0.95,secu=secu*0.95,rate=rate*0.95,def=def*0.95,wall=wall*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 삭제
        $query = "delete from diplomacy where me='{$me['nation']}' or you='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // NPC들 일부 하야 (양국중 큰쪽 장수수의 90~110%만큼)
        $resignCount = 0;
        if($npccount >= $npccount2) {
            $resignCount = round($npccount*(rand()%21+90)/100);
        } else {
            $resignCount = round($npccount2*(rand()%21+90)/100);
        }
        $resignCommand = EncodeCommand(0, 0, 0, 45); //하야
        $query = "update general set turn0='$resignCommand' where nation='{$you['nation']}' and npc>=2 order by rand() limit {$resignCount}";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushGenLog($me, $mylog);
        pushGenLog($you, $youlog);
        pushWorldHistory($history, $admin['year'], $admin['month']);
        unset($mylog);
        unset($youlog);
        unset($history);

        refreshNationStaticInfo();
    }
}

function checkSurrender($connect) {
    $query = "select year,month from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from diplomacy where state='5' and term='0'";
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
        $query = "select nation,name,surlimit,totaltech from nation where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $younation = MYDB_fetch_array($result);
        // 아국
        $query = "select nation,name,gold,rice,surlimit,totaltech from nation where nation='{$me['nation']}'";
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

        //피항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$mynation['name']}</b></> 합병에 성공했습니다.";
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></> 합병에 성공");
        }
        //항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation from general where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount2 = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>(으)로 항복하여 수도로 이동합니다.";
        for($i=0; $i < $gencount2; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            pushGeneralHistory($gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>가 <D><b>{$younation['name']}</b></>(으)로 항복");
        }

        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【투항】</b></><D><b>{$mynation['name']}</b></> (이)가 <D><b>{$younation['name']}</b></>(으)로 항복하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>【혼란】</b></>통합에 반대하는 세력들로 인해 <D><b>{$younation['name']}</b></>에 혼란이 일고 있습니다.";
        pushNationHistory($younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>(와)과 합병");

        $newGenCount = $gencount + $gencount2;
        if($newGenCount < 10) { $newGenCount = 10; }
        $newTotalTech = $younation['totaltech'] + $mynation['totaltech'];
        $newTech = round($newTotalTech / $newGenCount);
        // 자금 통합, 외교제한 5년, 기술유지
        $query = "update nation set gold=gold+'{$mynation['gold']}',rice=rice+'{$mynation['rice']}',surlimit='24',totaltech='$newTotalTech',tech='$newTech',gennum='{$newGenCount}' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //합병 당한국 모든 도시 10%감소
        $query = "update city set pop=pop*0.9,agri=agri*0.9,comm=comm*0.9,secu=secu*0.9,rate=rate*0.9,def=def*0.9,wall=wall*0.9 where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //합병 시도국 모든 도시 5%감소
        $query = "update city set pop=pop*0.95,agri=agri*0.95,comm=comm*0.95,secu=secu*0.95,rate=rate*0.95,def=def*0.95,wall=wall*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //국가 삭제
        $query = "delete from nation where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 군주가 있는 위치 구함
        $query = "select city from general where nation='{$you['nation']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($result);
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',gen1='0',gen2='0',gen3='0',conflict='',conflict2='' where nation='{$me['nation']}'";
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
            $resignCount = round($npccount*(rand()%21+90)/100);
        } else {
            $resignCount = round($npccount2*(rand()%21+90)/100);
        }
        $resignCommand = EncodeCommand(0, 0, 0, 45); //하야
        $query = "update general set turn0='$resignCommand' where nation='{$you['nation']}' and npc>=2 order by rand() limit {$resignCount}";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushGenLog($me, $mylog);
        pushGenLog($you, $youlog);
        pushWorldHistory($history, $admin['year'], $admin['month']);
        unset($mylog);
        unset($youlog);
        unset($history);

        refreshNationStaticInfo();
    }
}

function updateNationState($connect) {
    $history = array();
    $query = "select year,month from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,level from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);

        $query = "select city,level,secu from city where nation='{$nation['nation']}' and level>=4";
        $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $citycount = MYDB_num_rows($cityresult);

        $query = "select no from general where nation='{$nation['nation']}'";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

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
            $nation['level'] = $nationlevel;

            switch($nationlevel) {
                case 7:
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>(을)를 자칭하였습니다.";
                    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>(을)를 자칭");
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

            //작위 상승
            $query = "update nation set level='{$nation['level']}' where nation='{$nation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            refreshNationStaticInfo();
        }
        $gennum = $gencount;
        if($gencount < 10) $gencount = 10;
        //기술 및 변경횟수 업데이트
        $query = "update nation set tech=totaltech/'$gencount',gennum='$gennum' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
}

function checkStatistic($connect) {
    $query = "select year,month from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $etc = '';

    $query = "select avg(gold) as avggold, avg(rice) as avgrice, avg(dex0+dex10+dex20+dex30) as avgdex, max(dex0+dex10+dex20+dex30) as maxdex, avg(experience+dedication) as avgexpded, max(experience+dedication) as maxexpded from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    $general['avggold'] = round($general['avggold']);
    $general['avgrice'] = round($general['avgrice']);
    $general['avgdex'] = round($general['avgdex']);
    $general['avgexpded'] = round($general['avgexpded']);
    $etc .= "평균 금/쌀 ({$general['avggold']}/{$general['avgrice']}), 평균/최고 숙련({$general['avgdex']}/{$general['maxdex']}), 평균/최고 경험공헌({$general['avgexpded']}/{$general['maxexpded']}), ";

    $query = "select min(tech) as mintech, max(tech) as maxtech, avg(tech) as avgtech, min(power) as minpower, max(power) as maxpower, avg(power) as avgpower from nation where level>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);
    $nation['avgtech'] = round($nation['avgtech']);
    $nation['avgpower'] = round($nation['avgpower']);
    $etc .= "최저/평균/최고 기술({$nation['mintech']}/{$nation['avgtech']}/{$nation['maxtech']}), ";
    $etc .= "최저/평균/최고 국력({$nation['minpower']}/{$nation['avgpower']}/{$nation['maxpower']}), ";
    
    $nationName = '';
    $power_hist = '';

    $query = "select nation,name,type,power,gennum,round((gold+rice)/100) as goldrice from nation where level>0 order by power desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);
    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);

        $query = "select sum(leader+power+intel) as abil,round(sum(gold+rice)/100) as goldrice,round(sum(dex0+dex10+dex20+dex30)/1000) as dex,round(sum(experience+dedication)/100) as expded from general where nation='{$nation['nation']}'";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result2);

        $query = "select count(*) as cnt,round(sum(pop)/100) as pop,round(sum(pop2)/100) as pop2 from city where nation='{$nation['nation']}'";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result2);
        
        $nationName .= $nation['name'].'('.getNationType($nation['type']).'), ';
        $power_hist .= "{$nation['name']}({$nation['power']}/{$nation['gennum']}/{$city['cnt']}/{$city['pop']}/{$city['pop2']}/{$nation['goldrice']}/{$general['goldrice']}/{$general['abil']}/{$general['dex']}/{$general['expded']}), ";
        $nationHists[$nation['type']]++;
    }

    $nationHist = '';
    for($i=1; $i <= 13; $i++) {
        if(!$nationHists[$i]) { $nationHists[$i] = '-'; }
        $nationHist .= getNationType($i)."({$nationHists[$i]}), ";
    }

    $query = "select no from general where npc <= 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);

    $query = "select no from general where npc > 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $npcCount = MYDB_num_rows($result);

    $query = "select personal,special,special2 from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $generalCount = MYDB_num_rows($result);
    for($i=0; $i < $generalCount; $i++) {
        $general = MYDB_fetch_array($result);

        $personalHists[$general['personal']]++;
        $specialHists[$general['special']]++;
        $specialHists2[$general['special2']]++;
    }

    $generalCountStr = "{$generalCount}({$genCount}+{$npcCount})";

    $personalHist = '';
    for($i=0; $i < 11; $i++) {
        if(!$personalHists[$i]) { $personalHists[$i] = '-'; }
        $personalHist .= getGenChar($i)."({$personalHists[$i]}), ";
    }
    $specialHist = '';
    for($i=0; $i < 40; $i++) {
        $call = getGenSpecial($i);
        if($call) {
            if(!$specialHists[$i]) { $specialHists[$i] = '-'; }

            $specialHist .= $call."({$specialHists[$i]}), ";
        }
    }
    $specialHist .= '// ';
    $specialHist .= "-({$specialHists2[0]}), ";
    for($i=40; $i < 80; $i++) {
        $call = getGenSpecial($i);
        if($call) {
            if(!$specialHists2[$i]) { $specialHists2[$i] = '-'; }

            $specialHist .= $call."({$specialHists2[$i]}), ";
        }
    }

    $crewtype = '';
    $types = array(0, 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 20, 21, 22, 23, 24, 25, 26, 27, 30, 31, 32, 33, 34, 35, 36, 37, 38, 40, 41, 42, 43);
    $count = count($types);
    foreach(GameUnitConst::all() as $unit){
        $userCnt = DB::db()->queryFirstField(
            "SELECT count(*) as type from general where crewtype=%i and crew>=100",
            $unit->id
        );
        $crewtype .= "{$unit->name}({$userCnt}), ";
    }
    for($i=0; $i < $count; $i++) {
        
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);

        
    }

    $query = "
        insert into statistic (
            year, month,
            nation_count, nation_name, nation_hist,
            gen_count, personal_hist, special_hist, power_hist,
            crewtype, etc
        ) values (
            '{$admin['year']}', '{$admin['month']}',
            '$nationCount', '$nationName', '$nationHist',
            '$generalCountStr', '$personalHist', '$specialHist', '$power_hist',
            '$crewtype', '$etc'
        )";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function checkEmperior($connect) {
    $query = "select year,month,isUnited from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name from nation where level>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    if($count == 1 && $admin['isUnited'] == 0) {
        $nation = MYDB_fetch_array($result);

        $query = "select city from city where nation='{$nation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);

        $query = "select city from city";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $allcount = MYDB_num_rows($result);

        if($count == $allcount) {
            pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>(이)가 전토를 통일");

            $query = "update game set isUnited=2,conlimit=conlimit*100";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "select no from general where npc<2 and age>=45";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);

            for($i=0; $i < $count; $i++) {
                $general = MYDB_fetch_array($result);
                CheckHall($connect, $general['no']);
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

            $query = "select name,picture,killnum from general where nation='{$nation['nation']}' order by killnum desc limit 5";   // 오호장군
            $tigerresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $tigernum = MYDB_num_rows($tigerresult);
            for($i=0; $i < $tigernum; $i++) {
                $tiger = MYDB_fetch_array($tigerresult);
                if($tiger['killnum'] > 0) {
                    $tigerstr .= "{$tiger['name']}【{$tiger['killnum']}】, ";
                }
            }

            $query = "select name,picture,firenum from general where nation='{$nation['nation']}' order by firenum desc limit 7";   // 건안칠자
            $eagleresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $eaglenum = MYDB_num_rows($eagleresult);
            for($i=0; $i < $eaglenum; $i++) {
                $eagle = MYDB_fetch_array($eagleresult);
                if($eagle['firenum'] > 0) {
                    $eaglestr .= "{$eagle['name']}【{$eagle['firenum']}】, ";
                }
            }

            $log[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월: <D><b>{$nation['name']}</b></>(이)가 전토를 통일하였습니다.";

            $query = "select no,name from general where nation='{$nation['nation']}' order by dedication desc";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
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

            $query = "select gen_count,personal_hist,special_hist from statistic order by no desc limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $statGeneral = MYDB_fetch_array($result);

            $nationHistory = DB::db()->queryFirstField('SELECT `history` FROM `nation` WHERE `nation` = %i', $nation['nation']);

            $query = "
                insert into emperior (
                    phase,
                    nation_count, nation_name, nation_hist,
                    gen_count, personal_hist, special_hist,
                    name, type, color, year, month, power, gennum, citynum,
                    pop, poprate, gold, rice,
                    l12name, l12pic, l11name, l11pic,
                    l10name, l10pic, l9name, l9pic,
                    l8name, l8pic, l7name, l7pic,
                    l6name, l6pic, l5name, l5pic,
                    tiger, eagle, gen, history
                ) values (
                    '-',
                    '$statNC', '{$statNation['nation_name']}', '{$statNation['nation_hist']}',
                    '$statGC', '{$statGeneral['personal_hist']}', '{$statGeneral['special_hist']}',
                    '{$nation['name']}', '{$nation['type']}', '{$nation['color']}', '{$admin['year']}', '{$admin['month']}', '{$nation['power']}', '{$nation['gennum']}', '$allcount',
                    '$pop', '$poprate', '{$nation['gold']}', '{$nation['rice']}',
                    '{$level12['name']}', '{$level12['picture']}', '{$level11['name']}', '{$level11['picture']}',
                    '{$level10['name']}', '{$level10['picture']}', '{$level9['name']}', '{$level9['picture']}',
                    '{$level8['name']}', '{$level8['picture']}', '{$level7['name']}', '{$level7['picture']}',
                    '{$level6['name']}', '{$level6['picture']}', '{$level5['name']}', '{$level5['picture']}',
                    '$tigerstr', '$eaglestr', '$gen', '{$nationHistory}'
                )";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통일】</b></><D><b>{$nation['name']}</b></>(이)가 전토를 통일하였습니다.";
            pushWorldHistory($history, $admin['year'], $admin['month']);

            //연감 월결산
            LogHistory();
        }
    }
}
