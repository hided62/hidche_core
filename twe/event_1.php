<?php
include "lib.php";
include "func.php";
//////////////////////////장수성격//////////////////////////////////////////////
//은둔 안전 유지 재간 출세 할거 정복 패권 의협 대의 왕좌
////////////////////////////////////////////////////////////////////////

//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where no_member='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    echo "
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
</head>
<body>
관리자가 아닙니다.<br>
";
    banner();
    echo "
</body>
</html>";

    exit();
}

$query = "select year,month,turnterm,isUnited from game where no='1'";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

if($admin['isUnited'] == 0) {
    $query = "select no from general where npc<2 and age>50";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        CheckHall($connect, $general['no']);
    }
}

// 수도가 이성인경우 옆으로 이전, 또는 랜덤
$query = "select nation from nation where capital=63 or capital=64 or capital=65 or capital=66 or capital=67 or capital=68 or capital=69";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$count = MYDB_num_rows($result);

for($i=0; $i < $count; $i++) {
    $nation = MYDB_fetch_array($result);

    $query = "select city,nation from city where nation='{$nation['nation']}' order by rand() limit 0,1";
    $result2 = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    $city = MYDB_fetch_array($result2);

    $query = "update nation set capital='{$city['city']}' where nation='{$nation['nation']}'";
    MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
}

//이벤트1 : 이민족침범
$name = Array(  "강족", "저족", "흉노족", "남만족", "산월족", "오환족", "왜족");
$cap  = Array(      63,       64,     65,     66,       67,       68,     69);

$query = "select no from general where npc<5";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$eachCount = MYDB_num_rows($result);
$eachCount = round($eachCount / 7) * 2;

for($i=0; $i < 7; $i++) {
    RegNation($connect, $name[$i], "800000", 999999, 999999, "중원의 부패를 물리쳐라! 이민족 침범!", 15000, $eachCount, "병가", 0);
}

//////////////////////////외교//////////////////////////////////////////////////
$query = "update game set isUnited=1";
MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

//--------------------------------------------------------------------
for($i=0; $i < 7; $i++) {
    $query = "select nation from nation where name='{$name[$i]}'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $nationNum[$i] = $nation['nation'];

    $query = "update nation set scout=1,capital={$cap[$i]} where nation='{$nation['nation']}'";
    MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

    //태수,군사,시중 해제
    $query = "select gen1,gen2,gen3 from city where city='{$cap[$i]}'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);
    //태수 해제
    $query = "update general set level=1 where no='{$city['gen1']}'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    //군사 해제
    $query = "update general set level=1 where no='{$city['gen2']}'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    //시중 해제
    $query = "update general set level=1 where no='{$city['gen3']}'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

    $query = "update city set nation='{$nation['nation']}',pop='1000000',agri=agri2,comm=comm2,secu=secu2,def=def2,wall=wall2,supply=1,gen1=0,gen2=0,gen3=0 where city='{$cap[$i]}'";
    MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

    $query = "select nation,level from nation where nation!='{$nation['nation']}'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($k=0; $k < $count; $k++) {
        $your = MYDB_fetch_array($result);
        if($your['level'] > 0) {
            $query = "insert into diplomacy (me, you, state, term) values ('{$nation['nation']}', '{$your['nation']}', '1', '1')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "insert into diplomacy (me, you, state, term) values ('{$your['nation']}', '{$nation['nation']}', '1', '1')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "insert into diplomacy (me, you, state, term) values ('{$nation['nation']}', '{$your['nation']}', '7', '999')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "insert into diplomacy (me, you, state, term) values ('{$your['nation']}', '{$nation['nation']}', '7', '999')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
}

for($i=0; $i < 7; $i++) {
    $query = "update nation set level=1 where nation='$nationNum[$i]'";
    MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
}
//--------------------------------------------------------------------

//////////////////////////장수//////////////////////////////////////////////////
//                                                                          이름   통  무  지    꿈   특기
$gencount = 2001;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[0],12,    "강대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[1],12,    "저대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[2],12,  "흉노대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[3],12,  "남만대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[4],12,  "산월대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[5],12,  "오환대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[6],12,    "왜대왕", 95, 95, 75,"패권","돌격", ""); $gencount++;

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[0], 1,  "강장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[1], 1,  "저장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[2], 1,"흉노장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[3], 1,"남만장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[4], 1,"산월장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[5], 1,"오환장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

for($k=1; $k <= $eachCount; $k++) {
    if(rand()%2) { $l = rand()%10 + 85; $p = rand()%10 + 85; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 85; $p = rand()%10 + 10; $i = rand()%10 + 85; }
    RegGeneral3($connect,$admin['turnterm'],$gencount,$nationNum[6], 1,  "왜장수{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

//////////////////////////장수 끝///////////////////////////////////////////////

// 전장수 금쌀 999999
$query = "update general set gold=999999,rice=999999";
MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

//////////////////////////이벤트///////////////////////////////////////////////
$history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【이벤트】</b></>각지의 이민족들이 <M>궐기</>합니다!";
$history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【이벤트】</b></>중원의 전 국가에 <M>선전포고</> 합니다!";
$history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【이벤트】</b></>이민족의 기세는 그 누구도 막을 수 없을듯 합니다!";
pushHistory($connect, $history);

echo "<script>location.replace('./');</script>";

function RegGeneral3($connect,$turnterm,$gencount,$nation,$level,$name,$leader,$power,$intel,$personal,$special,$msg="") {
    $name = "ⓞ".$name;
    $turntime = getRandTurn2($turnterm);
    $personal = CharCall($personal);
    $special = SpecCall($special);
    if($special >= 40) { $special2 = $special; $special = 0; }
    else { $special2 = 0; }
    $city = rand()%94 + 1;
    $age = 15;
    $specage = $age;
    $specage2 = $age;
    $killturn = 9999;
    $experience = $age * 100;
    $dedication = $age * 100;
    $npc = 5;
    $npcmatch = 999;
    $picture = 'default.jpg';
    $crew = $leader * 100;
    $crewtype = rand() % 3;
    //장수
    @MYDB_query("
        insert into general (
            npcid,npc,npc_org,npcmatch,name,picture,nation,city,
            leader,power,intel,experience,dedication,
            level,gold,rice,crew,crewtype,train,atmos,tnmt,
            weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
            makelimit,
            dex0, dex10, dex20, dex30, dex40
        ) values (
            '$gencount','$npc','$npc','$npcmatch','$name','$picture','$nation','$city',
            '$leader','$power','$intel','$experience','$dedication',
            '$level','99999','99999','$crew','$crewtype','100','100','0',
            '6','6','6','$turntime','$killturn','$age','1',
            '$personal','$special','$specage','$special2','$specage2','$msg',
            '0',
            450000, 450000, 450000, 450000, 450000
        )",
        $connect
    ) or Error(__LINE__.MYDB_error($connect),"");
}

