<?php
include "lib.php";
include "func.php";

//NOTE:폐기.

//////////////////////////장수성격//////////////////////////////////////////////
//은둔 안전 유지 재간 출세 할거 정복 패권 의협 대의 왕좌
////////////////////////////////////////////////////////////////////////

//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where owner='{$_SESSION['noMember']}'";
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
    echo banner();
    echo "
</body>
</html>";

    exit();
}

//중복체크
$query = "select no from general where user_id='jwh1807'";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$general = MYDB_fetch_array($result);

if($general['no'] > 0) {
    echo "
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
</head>
<body>
이미 있습니다.<br>
";
    echo banner();
    echo "
</body>
</html>";

    exit();
}

$query = "select year,month,turnterm,isUnited from game where no='1'";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select round(avg(specage)) as specage,round(avg(specage2)) as specage2 from general where npc<2";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$general = MYDB_fetch_array($result);

//이벤트4 : 유기체 추가
//////////////////////////장수//////////////////////////////////////////////////
//                                                                          이름   통  무  지    꿈   특기
$gencount = 2000;

$personal = rand()%10;
$personal = getGenChar($personal);

$leader = 65 + rand()%11;
if(rand()%2 == 0) {
    $intel = 10 + rand()%6;
    $power = 150 - $leader - $intel;
} else {
    $power= 10 + rand()%6;
    $intel = 150 - $leader - $power;
}

RegGeneral4($connect,$admin['turnterm'],$gencount, 0, 0,    "유기체", $leader, $power, $intel, $personal, $general['specage'], $general['specage2'], "흠... 그럼 어쩔 수 없이 흉노로 가야겠군요."); $gencount++;

//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////
$log[0] = "<C>●</>{$admin['month']}월:<Y>ⓝ유기체</>가 천하에 이름을 알립니다.";
pushAllLog($log);

$history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【이벤트】</b></>NPC 유기체가 등장합니다. 의병장과 NPC들의 지능 개선을 위해 NPC 두뇌를 체험합니다. 크게 신경쓰진 마세요.";
pushHistory($history);

//echo "<script>location.replace('./');</script>";
echo './';//TODO:debug all and replace

function RegGeneral4($connect,$turnterm,$gencount,$nation,$level,$name,$leader,$power,$intel,$personal,$specage,$specage2,$msg="") {
    $name = "ⓝ".$name;
    $turntime = getRandTurn($turnterm);
    $personal = CharCall($personal);
    $city = rand()%94 + 1;
    $age = 20;

    //전특
    $special2 = 0;
    //내특
    $special = 0;

    $killturn = 720;
    $experience = 0;
    $dedication = 0;
    $npc = 3;
    $npcmatch = rand() % 150 + 1;
    $picture = 'pic_3.jpg';
    $crew = 0;
    $crewtype = rand() % 3;
    $dex0 = 0;
    $dex10 = 0;
    $dex20 = 0;
    $dex30 = 0;
    $dex40 = 0;
    //장수
    @MYDB_query("
        insert into general (
            npcid,npc,npc_org,npcmatch,name,picture,nation,city,
            leader,power,intel,experience,dedication,
            level,gold,rice,crew,crewtype,train,atmos,
            weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
            makelimit,
            dex0, dex10, dex20, dex30, dex40
        ) values (
            '$gencount','$npc','$npc','$npcmatch','$name','$picture','$nation','$city',
            '$leader','$power','$intel','$experience','$dedication',
            '$level','1000','1000','$crew','$crewtype','100','100',
            '0','0','0','$turntime','$killturn','$age','1',
            '$personal','$special','$specage','$special2','$specage2','$msg',
            '0',
            '$dex0', '$dex10', '$dex20', '$dex30', '$dex40'
        )",
        $connect
    ) or Error(__LINE__.MYDB_error($connect),"");
}

