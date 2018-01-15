<?php
//////////////////////////장수성격//////////////////////////////////////////////
//은둔 안전 유지 재간 출세 할거 정복 패권 의협 대의 왕좌
////////////////////////////////////////////////////////////////////////

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    echo "
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
</head>
<body oncontextmenu='return false'>
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

//이벤트3 : NPC추가
//////////////////////////장수//////////////////////////////////////////////////
//                                                                          이름   통  무  지    꿈   특기
$gencount = 2001;
for($k=1; $k <= 200; $k++) {
    if(rand()%2) { $l = rand()%40 + 35; $p = rand()%40 + 35; $i = rand()%10 + 10; }
    else         { $l = rand()%40 + 35; $p = rand()%10 + 10; $i = rand()%40 + 35; }
    RegGeneral3($connect,$admin['turnterm'],$gencount, 0, 0,  "무명장{$k}", $l, $p, $i,"패권","돌격", ""); $gencount++;
}

//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////
$history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【이벤트】</b></>각지에서 인재들이 <M>등장</>합니다!";
$history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【이벤트】</b></>중원 통일에 보탬이 될듯 합니다!";
pushHistory($connect, $history);

echo "<script>location.replace('./');</script>";

function RegGeneral3($connect,$turnterm,$gencount,$nation,$level,$name,$leader,$power,$intel,$personal,$special,$msg="") {
    $name = "ⓝ".$name;
    $genid      = "npc{$gencount}";
    $turntime = getRandTurn($turnterm);
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
    $npc = 2;
    $npcmatch = rand() % 150 + 1;
    $picture = 'default.jpg';
    $crew = 0;
    $crewtype = rand() % 3;
    $dex0 = rand() % 18000;
    $dex10 = rand() % 18000;
    $dex20 = rand() % 18000;
    $dex30 = rand() % 18000;
    $dex40 = rand() % 18000;
    $pw = md5("18071807");
    //장수
    @MYDB_query("
        insert into general (
            npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,city,
            leader,power,intel,experience,dedication,
            level,gold,rice,crew,crewtype,train,atmos,tnmt,
            weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
            makelimit,
            dex0, dex10, dex20, dex30, dex40
        ) values (
            '$gencount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','$nation','$city',
            '$leader','$power','$intel','$experience','$dedication',
            '$level','1000','1000','$crew','$crewtype','100','100','0',
            '0','0','0','$turntime','$killturn','$age','1',
            '$personal','$special','$specage','$special2','$specage2','$msg',
            '0',
            '$dex0', '$dex10', '$dex20', '$dex30', '$dex40'
        )",
        $connect
    ) or Error(__LINE__.MYDB_error($connect),"");
}

