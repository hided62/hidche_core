<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$count = sizeof($turn);
for($i=0; $i < $count; $i++) {
    if($turn[$i] == 100 || $turn[$i] == 99 || $turn[$i] == 98) {
    } elseif($turn[$i] >= 0 || $turn[$i] <= 23) {
    } else {
        unset($turn);
        $turn[0] = 100;
        break;
    }
}

if($turn[0] == 100) {
    unset($turn);
    for($i=0; $i < 24; $i++) $turn[$i] = $i;
} elseif($turn[0] == 99) {
    unset($turn);
    for($i=0, $j=0; $i < 24; $i+=2, $j++) $turn[$j] = $i;
} elseif($turn[0] == 98) {
    unset($turn);
    for($i=1, $j=0; $i < 24; $i+=2, $j++) $turn[$j] = $i;
}

switch($commandtype) {
    case  0: command_Single($connect, $turn, 0); break; //휴식
    case  1: command_Single($connect, $turn, 1); break; //농업
    case  2: command_Single($connect, $turn, 2); break; //상업
    case  3: command_Single($connect, $turn, 3); break; //기술
    case  4: command_Single($connect, $turn, 4); break; //선정
    case  5: command_Single($connect, $turn, 5); break; //수비
    case  6: command_Single($connect, $turn, 6); break; //성벽
    case  7: command_Single($connect, $turn, 7); break; //정착 장려
    case  8: command_Single($connect, $turn, 8); break; //치안 강화
    case  9: command_Single($connect, $turn, 9); break; //자금 조달

    case 11: command_11($connect,     $turn, 11); break; //징병
    case 12: command_12($connect,     $turn, 12); break; //모병
    case 13: command_Single($connect, $turn, 13); break; //훈련
    case 14: command_Single($connect, $turn, 14); break; //사기진작
    //case 15: command_Single($connect, $turn, 0); break; //전투태세 
    case 16: command_16($connect,     $turn, 16); break; //전쟁
    case 17: command_Single($connect, $turn, 17); break; //소집해제

    case 21: command_21($connect,     $turn, 21); break; //이동
    //case 22: command_22($connect,     $turn, 22); break; //등용 //TODO:등용장 재 디자인
    case 23: command_23($connect,     $turn, 23); break; //포상
    case 24: command_24($connect,     $turn, 24); break; //몰수
    case 25: command_25($connect,     $turn, 25); break; //임관
    case 26: command_Single($connect, $turn, 26); break; //집합
    case 27: command_27($connect,     $turn, 27); break; //발령
    case 28: command_Single($connect, $turn, 28); break; //귀환
    case 29: command_Single($connect, $turn, 29); break; //인재탐색
    case 30: command_30($connect,     $turn, 30); break; //강행
    
    case 31: command_31($connect, $turn, 31); break; //첩보
    case 32: command_32($connect, $turn, 32); break; //화계
    case 33: command_33($connect, $turn, 33); break; //탈취
    case 34: command_34($connect, $turn, 34); break; //파괴
    case 35: command_35($connect, $turn, 35); break; //선동
    case 36: command_36($connect, $turn, 36); break; //기습

    case 41: command_Single($connect, $turn, 41); break; //단련
    case 42: command_Single($connect, $turn, 42); break; //견문
    case 43: command_43($connect,     $turn, 43); break; //증여
    case 44: command_44($connect,     $turn, 44); break; //헌납
    case 45: command_Single($connect, $turn, 45); break; //하야
    case 46: command_46($connect,     $turn, 46); break; //건국
    case 47: command_Single($connect, $turn, 47); break; //방랑
    case 48: command_48($connect,     $turn, 48); break; //장비구입
    case 49: command_49($connect,     $turn, 49); break; //군량매매
    case 50: command_Single($connect, $turn, 50); break; //요양

    case 51: command_51($connect, $turn, 51); break; //항복권고
    case 52: command_52($connect, $turn, 52); break; //원조
    case 53: command_53($connect, $turn, 53); break; //통합제의
    case 54: command_54($connect, $turn, 54); break; //선양
    case 55: command_Single($connect, $turn, 55); break; //거병
    case 56: command_Single($connect, $turn, 56); break; //해산
    case 57: command_Single($connect, $turn, 57); break; //모반 시도

    case 61: command_61($connect, $turn, 61); break; //불가침
    case 62: command_62($connect, $turn, 62); break; //선포
    case 63: command_63($connect, $turn, 63); break; //종전
    case 64: command_64($connect, $turn, 64); break; //파기
    case 65: command_65($connect, $turn, 65); break; //초토화
    case 66: command_66($connect, $turn, 66); break; //천도
    case 67: command_67($connect, $turn, 67); break; //증축
    case 68: command_68($connect, $turn, 68); break; //감축
    case 71: command_Chief($connect, $turn, 71); break; //필사즉생
    case 72: command_72($connect, $turn, 72); break; //백성동원
    case 73: command_73($connect, $turn, 73); break; //수몰
    case 74: command_74($connect, $turn, 74); break; //허보
    case 75: command_75($connect, $turn, 75); break; //피장파장
    case 76: command_Chief($connect, $turn, 76); break; //의병모집
    case 77: command_77($connect, $turn, 77); break; //이호경식
    case 78: command_78($connect, $turn, 78); break; //급습
    case 81: command_81($connect, $turn, 81); break; //국기변경
    case 99: command_99($connect, $turn); break; //수뇌부 휴식
    
    default:command_Single($connect, $turn, 0); break; //휴식
}

function starter($name, $type=0) {
    //FIXME: 장기적으로 template로 변경해야함.
    global $_basecolor2, $_basecolor4;
    global $images;
    echo '
<!DOCTYPE html>
<html>
<head>
<title>'.$name.'</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="js/common.js"></script>
<script src="js/main.js"></script>
<script src="js/base_map.js"></script>
<script src="js/map.js"></script>
<script>
$(function(){
    var $target = $("form[name=form1] select[name=double]");
    console.log($target);
    reloadWorldMap({
        isDetailMap:false,
        clickableAll:true,
        neutralView:true,
        selectCallback:function(city){
            $target.val(city.id);
            return false;
        }
    });
});
</script>
<link href="css/normalize.css" rel="stylesheet">
<link href="css/common.css" rel="stylesheet">
<link href="css/main.css" rel="stylesheet">
<link href="css/map.css" rel="stylesheet">
';
echo '
</head>
<body>
<table class="bg0" align="center" width="1000" border="1" cellspacing="0" cellpadding="0" bordercolordark="gray" bordercolorlight="black" style="font-size:13px;word-break:break-all;">
    <tr><td class="bg1" align="center">'.$name.'</td></tr>
    <tr><td>
';
    if($type == 1) echo CoreBackButton();
    else echo backButton();
}

function ender($type=0) {
    if($type == 1) echo CoreBackButton();
    else echo backButton();
    echo banner();
    echo "
    </td></tr>
</table>";

    PrintElapsedTime();

    echo "
</body>
</html>
";
}

function command_99($connect, $turn) {
    $query = "select nation,level from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $command = EncodeCommand(0, 0, 0, 99);

        for($i=0; $i < sizeof($turn); $i++) {
            $query = "update nation set l{$me['level']}turn{$turn[$i]}='{$command}' where nation='{$me['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
    //echo "<script>location.replace('b_chiefcenter.php');</script>";
    echo 'b_chiefcenter.php';//TODO:debug all and replace
}

function GetExplain() {
    $explain[0] = "표준적인 보병입니다.<br>보병은 방어특화입니다.";
    $explain[1] = "저렴하고 튼튼합니다.<br>중원지역 소유시 가능.";
    $explain[2] = "저렴하고 강력합니다.<br>오월지역 소유시 가능.";
    $explain[3] = "은밀하고 날쌥니다.<br>저 소유시 가능.";
    $explain[4] = "최강의 보병입니다.<br>낙양 소유시 가능.";
    $explain[5] = "등갑을 두른 보병입니다.<br>남중지역 소유시 가능.";

    $explain[10] = "표준적인 궁병입니다.<br>궁병은 회피특화입니다.";
    $explain[11] = "말을 타고 잘 피합니다.<br>동이지역 소유시 가능.";
    $explain[12] = "화살을 연사합니다.<br>서촉지역 소유시 가능.";
    $explain[13] = "강건한 궁병입니다.<br>양양 소유시 가능.";
    $explain[14] = "강력한 화살을 쏩니다.<br>건업 소유시 가능.";

    $explain[20] = "표준적인 기병입니다.<br>기병은 공격특화입니다.";
    $explain[21] = "백마의 위용을 보여줍니다.<br>하북지역 소유시 가능.";
    $explain[22] = "갑주를 두른 기병입니다.<br>서북지역 소유시 가능.";
    $explain[23] = "저돌적으로 공격합니다.<br>흉노 소유시 가능.";
    $explain[24] = "철갑을 두른 기병입니다.<br>강 소유시 가능.";
    $explain[25] = "날쎄고 빠른 기병입니다.<br>산월 소유시 가능.";
    $explain[26] = "어느 누구보다 강력합니다.<br>남만 소유시 가능.";
    $explain[27] = "정예 기병입니다.<br>허창 소유시 가능.";

    $explain[30] = "계략을 사용하는 병종입니다.<br>귀병은 계략특화입니다.";
    $explain[31] = "신출귀몰한 귀병입니다.<br>초지역 소유시 가능.";
    $explain[32] = "저렴하고 튼튼합니다.<br>오환 소유시 가능.";
    $explain[33] = "저렴하고 강력합니다.<br>왜 소유시 가능.";
    $explain[34] = "백병전에도 능숙합니다.<br>장안 소유시 가능.";
    $explain[35] = "전투를 포기하고 계략에 몰두합니다.<br>귀병은 계략특화입니다.";
    $explain[36] = "고도로 훈련된 귀병입니다.<br>낙양 소유시 가능.";
    $explain[37] = "갑주를 두른 귀병입니다.<br>성도 소유시 가능.";
    $explain[38] = "날카로운 무기를 가진 귀병입니다.<br>업 소유시 가능.";

    $explain[40] = "높은 구조물 위에서 공격합니다.<br>차병은 공성특화입니다.";
    $explain[41] = "엄청난 위력으로 성벽을 부수어버립니다.<br>차병은 공성특화입니다.";
    $explain[42] = "상대에게 돌덩이를 날립니다.<br>업 소유시 가능.";
    $explain[43] = "상대를 저지하는 특수병기입니다.<br>성도 소유시 가능.";

    return $explain;
}

function command_11($connect, $turn, $command) {
    global $_basecolor, $_basecolor2, $images, $image;
    starter("징병");
    $query = "select * from game limit 1";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,nation,level,personal,special2,level,city,crew,horse,injury,leader,crewtype,gold from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select level,tech from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("process53 ".MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($me['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($me['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    $ownCities = [];
    $ownRegions = [];
    $relativeYear = $admin['year'] - $admin['startyear'];
    $tech = $nation['tech'];

    foreach(DB::db()->query('SELECT city, region from city where nation = %i', $me['nation']) as $city){
        $ownCities[$city['city']] = 1;
        $ownRegions[$city['region']] = 1;
    }

    $leader = floor($me['leader'] * (100 - $me['injury'])/100) + getHorseEff($me['horse']) + $lbonus;
    $crew = $leader - round($me['crew']/100);
    $abil = getTechAbil($nation['tech']);
    $cost = getTechCost($nation['tech']);
    echo "
<font size=2>병사를 모집합니다. 훈련과 사기치는 낮지만 가격이 저렴합니다.<br>
가능한 수보다 많게 입력하면 가능한 최대 병사를 모집합니다.<br>
이미 병사가 있는 경우 추가징병되며, 병종이 다를경우는 기존의 병사는 소집해제됩니다.<br>
현재 징병 가능한 병종은 <font color=green>녹색</font>으로 표시되며,<br>
현재 징병 가능한 특수병종은 <font color=limegreen>초록색</font>으로 표시됩니다.<br>
<script type='text/javascript'>
function calc(cost, formnum) {
    switch(formnum) {
        case 0: form = document.form0; break;
        case 1: form = document.form1; break;
        case 2: form = document.form2; break;
        case 3: form = document.form3; break;
        case 4: form = document.form4; break;
        case 5: form = document.form5; break;
        case 10: form = document.form10; break;
        case 11: form = document.form11; break;
        case 12: form = document.form12; break;
        case 13: form = document.form13; break;
        case 14: form = document.form14; break;
        case 20: form = document.form20; break;
        case 21: form = document.form21; break;
        case 22: form = document.form22; break;
        case 23: form = document.form23; break;
        case 24: form = document.form24; break;
        case 25: form = document.form25; break;
        case 26: form = document.form26; break;
        case 27: form = document.form27; break;
        case 30: form = document.form30; break;
        case 31: form = document.form31; break;
        case 32: form = document.form32; break;
        case 33: form = document.form33; break;
        case 34: form = document.form34; break;
        case 35: form = document.form35; break;
        case 36: form = document.form36; break;
        case 37: form = document.form37; break;
        case 38: form = document.form38; break;
        case 40: form = document.form40; break;
        case 41: form = document.form41; break;
        case 42: form = document.form42; break;
        case 43: form = document.form43; break;
    }
    crew = form.double.value;
    form.cost.value = Math.round(crew * cost);
}
</script>
<table border=1 cellspacing=0 bordercolordark=gray bordercolorlight=black>
    <tr>
        <td colspan=10 align=center id=bg2>
            현재 기술력 : <input type=text style=text-align:right;color:white;background-color:black size=5 readonly value=".getTechCall($nation['tech']).">
            현재 통솔 : <input type=text style=text-align:right;color:white;background-color:black size=3 readonly value=$leader>
            현재 병종 : <input type=text size=8 style=text-align:right;color:white;background-color:black readonly value=".GameUnitConst::byID($me['crewtype'])->name.">
            현재 병사 : <input type=text size=5 style=text-align:right;color:white;background-color:black readonly value={$me['crew']}>
            현재 자금 : <input type=text size=5 style=text-align:right;color:white;background-color:black readonly value={$me['gold']}>
        </td>
    </tr>
    <tr>
        <td width=64 align=center id=bg1>사진</td>
        <td width=64 align=center id=bg1>병종</td>
        <td width=40 align=center id=bg1>공격</td>
        <td width=40 align=center id=bg1>방어</td>
        <td width=40 align=center id=bg1>기동</td>
        <td width=40 align=center id=bg1>회피</td>
        <td width=40 align=center id=bg1>군량</td>
        <td width=40 align=center id=bg1>가격</td>
        <td width=130 align=center id=bg1>병사수</td>
        <td width=300 align=center id=bg1>특징</td>
    </tr>";

    foreach(GameUnitConst::all() as $i=>$unit) {
        if($i == 0){
            echo '<tr><td colspan=10>보병 계열</td></tr>';
        }
        else if($i == 10){
            echo '<tr><td colspan=10>궁병 계열</td></tr>';
        }
        else if($i == 20){
            echo '<tr><td colspan=10>기병 계열</td></tr>';
        }
        else if($i == 30){
            echo '<tr><td colspan=10>귀병 계열</td></tr>';
        }
        else if($i == 40){
            echo '<tr><td colspan=10>차병 계열</td></tr>';
        }

        if(!$unit->isValid($ownCities, $ownRegions, $relativeYear, $tech)){
            continue; //TODO: 불가능한 병종도 보여줄 필요가 있음.
        }
        
        if($unit->recruitType == 0){
            $l = 'green';
        }
        else{
            $l = 'limegreen';
        }

        $baseRice = $unit->rice * $cost;
        $baseCost = $unit->cost * $cost;
        $baseCost = CharCost($baseCost, $me['personal']);

        $unitBaseType = intdiv($this->id, 10);
        if($me['special2'] == 50 && $unitBaseType == 0){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 51 && $unitBaseType == 1){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 52 && $unitBaseType == 2){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 53 && $unitBaseType == 3){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 54 && $unitBaseType == 4){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 72) { 
            $baseCost *= 0.5; 
        }

        $name = $this->name;
        $attack = $this->attack + $abil;
        $defence = $this->defence + $abil;
        $speed = $this->speed;
        $avoid = $this->avoid;
        $weapImage = "{$images}/weap{$i}.jpg";
        if($admin['show_img_level'] < 2) { $weapImage = "{$image}/default.jpg"; }
        
        $baseRiceShort = round($baseRice, 1);
        $baseCostShort = round($baseCost, 1);

        $info = join('<br>', $this->info);

        echo "
<tr height=64 bgcolor=$l>
    <td background={$weapImage} align=center></td>
    <td align=center>{$name}</td>
    <td align=center>{$attack}</td>
    <td align=center>{$defence}</td>
    <td align=center>{$speed}</td>
    <td align=center>{$avoid}</td>
    <td align=center>{$baseRiceShort}</td>
    <td align=center>{$baseCostShort}</td>
<form name=form{$i} action=c_double.php>
    <td valign=center>
        <input type=text name=double maxlength=3 size=3 value=$crew style=text-align:right;color:white;background-color:black>00명<input type=button value=계산 onclick='calc($baseCost, $i)'><br>
        <input type=text name=cost maxlength=5 size=5 readonly style=text-align:right;color:white;background-color:black>원 <input type=submit value=징병>
        <input type=hidden name=third value={$i}>
        <input type=hidden name=command value=$command>";

        for($j=0; $j < sizeof($turn); $j++) {
            echo "
        <input type=hidden name=turn[] value=$turn[$j]>";
        }
        echo "
    </td>
</form>
    <td>$info</td>
    </tr>";
    }

    echo "
</table>";

    ender();
}

function command_12($connect, $turn, $command) {
    global $_basecolor, $_basecolor2, $images, $image;
    starter("모병");
    $query = "select * from game limit 1";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,nation,level,personal,special2,level,city,crew,horse,injury,leader,crewtype,gold from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select level,tech from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("process53 ".MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($me['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($me['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    $ownCities = [];
    $ownRegions = [];
    $relativeYear = $admin['year'] - $admin['startyear'];
    $tech = $nation['tech'];

    foreach(DB::db()->query('SELECT city, region from city where nation = %i', $me['nation']) as $city){
        $ownCities[$city['city']] = 1;
        $ownRegions[$city['region']] = 1;
    }

    $leader = floor($me['leader'] * (100 - $me['injury'])/100) + getHorseEff($me['horse']) + $lbonus;
    $crew = $leader - round($me['crew']/100);
    $abil = getTechAbil($nation['tech']);
    $cost = getTechCost($nation['tech']);
    echo "
<font size=2>병사를 모집합니다. 훈련과 사기치는 높지만 자금이 많이 듭니다.<br>
가능한 수보다 많게 입력하면 가능한 최대 병사를 모집합니다.<br>
이미 병사가 있는 경우 추가징병되며, 병종이 다를경우는 기존의 병사는 소집해제됩니다.<br>
현재 모병 가능한 병종은 <font color=green>녹색</font>으로 표시되며,<br>
현재 모병 가능한 특수병종은 <font color=limegreen>초록색</font>으로 표시됩니다.<br>
<script type='text/javascript'>
function calc(cost, formnum) {
    switch(formnum) {
        case 0: form = document.form0; break;
        case 1: form = document.form1; break;
        case 2: form = document.form2; break;
        case 3: form = document.form3; break;
        case 4: form = document.form4; break;
        case 5: form = document.form5; break;
        case 10: form = document.form10; break;
        case 11: form = document.form11; break;
        case 12: form = document.form12; break;
        case 13: form = document.form13; break;
        case 14: form = document.form14; break;
        case 20: form = document.form20; break;
        case 21: form = document.form21; break;
        case 22: form = document.form22; break;
        case 23: form = document.form23; break;
        case 24: form = document.form24; break;
        case 25: form = document.form25; break;
        case 26: form = document.form26; break;
        case 27: form = document.form27; break;
        case 30: form = document.form30; break;
        case 31: form = document.form31; break;
        case 32: form = document.form32; break;
        case 33: form = document.form33; break;
        case 34: form = document.form34; break;
        case 35: form = document.form35; break;
        case 36: form = document.form36; break;
        case 37: form = document.form37; break;
        case 38: form = document.form38; break;
        case 40: form = document.form40; break;
        case 41: form = document.form41; break;
        case 42: form = document.form42; break;
        case 43: form = document.form43; break;
    }
    crew = form.double.value;
    form.cost.value = Math.round(crew * cost * 2);
}
</script>
<table border=1 cellspacing=0 bordercolordark=gray bordercolorlight=black>
    <tr><td align=center colspan=10>모병은 가격 2배의 자금이 소요됩니다.</td></tr>
    <tr>
        <td colspan=10 align=center id=bg2>
            현재 기술력 : <input type=text style=text-align:right;color:white;background-color:black size=5 readonly value=".getTechCall($nation['tech']).">
            현재 통솔 : <input type=text style=text-align:right;color:white;background-color:black size=3 readonly value=$leader>
            현재 병종 : <input type=text size=8 style=text-align:right;color:white;background-color:black readonly value=".GameUnitConst::byID($me['crewtype'])->name.">
            현재 병사 : <input type=text size=5 style=text-align:right;color:white;background-color:black readonly value={$me['crew']}>
            현재 자금 : <input type=text size=5 style=text-align:right;color:white;background-color:black readonly value={$me['gold']}>
        </td>
    </tr>
    <tr>
        <td width=64 align=center id=bg1>사진</td>
        <td width=64 align=center id=bg1>병종</td>
        <td width=40 align=center id=bg1>공격</td>
        <td width=40 align=center id=bg1>방어</td>
        <td width=40 align=center id=bg1>기동</td>
        <td width=40 align=center id=bg1>회피</td>
        <td width=40 align=center id=bg1>군량</td>
        <td width=40 align=center id=bg1>가격</td>
        <td width=130 align=center id=bg1>병사수</td>
        <td width=300 align=center id=bg1>특징</td>
    </tr>";

    
    foreach(GameUnitConst::all() as $i=>$unit) {
        if($i == 0){
            echo '<tr><td colspan=10>보병 계열</td></tr>';
        }
        else if($i == 10){
            echo '<tr><td colspan=10>궁병 계열</td></tr>';
        }
        else if($i == 20){
            echo '<tr><td colspan=10>기병 계열</td></tr>';
        }
        else if($i == 30){
            echo '<tr><td colspan=10>귀병 계열</td></tr>';
        }
        else if($i == 40){
            echo '<tr><td colspan=10>차병 계열</td></tr>';
        }

        if(!$unit->isValid($ownCities, $ownRegions, $relativeYear, $tech)){
            continue; //TODO: 불가능한 병종도 보여줄 필요가 있음.
        }
        
        if($unit->recruitType == 0){
            $l = 'green';
        }
        else{
            $l = 'limegreen';
        }

        $baseRice = $unit->rice * $cost;
        $baseCost = $unit->cost * $cost;
        $baseCost = CharCost($baseCost, $me['personal']);

        $unitBaseType = intdiv($this->id, 10);
        if($me['special2'] == 50 && $unitBaseType == 0){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 51 && $unitBaseType == 1){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 52 && $unitBaseType == 2){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 53 && $unitBaseType == 3){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 54 && $unitBaseType == 4){
            $baseCost *= 0.9;
        }
        else if($me['special2'] == 72) { 
            $baseCost *= 0.5; 
        }

        $name = $this->name;
        $attack = $this->attack + $abil;
        $defence = $this->defence + $abil;
        $speed = $this->speed;
        $avoid = $this->avoid;
        $weapImage = "{$images}/weap{$i}.jpg";
        if($admin['show_img_level'] < 2) { $weapImage = "{$image}/default.jpg"; }
        
        $baseRiceShort = round($baseRice, 1);
        $baseCostShort = round($baseCost, 1);

        $info = join('<br>', $this->info);

        echo "
<tr height=64 bgcolor=$l>
    <td background={$weapImage} align=center></td>
    <td align=center>{$name}</td>
    <td align=center>{$attack}</td>
    <td align=center>{$defence}</td>
    <td align=center>{$speed}</td>
    <td align=center>{$avoid}</td>
    <td align=center>{$baseRiceShort}</td>
    <td align=center>{$baseCostShort}</td>
<form name=form{$i} action=c_double.php>
    <td valign=center>
        <input type=text name=double maxlength=3 size=3 value=$crew style=text-align:right;color:white;background-color:black>00명<input type=button value=계산 onclick='calc($baseCost, $i)'><br>
        <input type=text name=cost maxlength=5 size=5 readonly style=text-align:right;color:white;background-color:black>원 <input type=submit value=징병>
        <input type=hidden name=third value={$i}>
        <input type=hidden name=command value=$command>";

        for($j=0; $j < sizeof($turn); $j++) {
            echo "
        <input type=hidden name=turn[] value=$turn[$j]>";
        }
        echo "
    </td>
</form>
    <td>$info</td>
    </tr>";
    }

    echo "
</table>";

    ender();
}

function command_15($connect, $turn, $command) {
    starter("재편성");
    $query = "select no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    $me = $general['no'];

    $query = "select no,name from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
자신의 병사를 다른 장수에게 보냅니다.
<form name=form1 action=c_double.php method=post>
<select name=third size=1>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($me != $general['no']) {
            echo "
    <option value={$general['no']}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<input type=text name=double size=3 maxlength=3 style=text-align:right;color:white;background-color:black>00명
<input type=submit value=재편성>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_16($connect, $turn, $command) {
    starter("출병");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 침공을 합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=출병>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 1);

    ender();
}

function command_21($connect, $turn, $command) {
    starter("이동");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 이동합니다.<br>
인접 도시로만 이동이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=이동>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 1);

    ender();
}

function command_22($connect, $turn, $command) {
    die('비활성화됨');//TODO:등용장 대 디자인
    starter("등용");

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
재야나 타국의 장수를 등용합니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
등용할 장수를 목록에서 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    $query = "select no,name,npc from general where nation='0' and npc<2 order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    if($gencount != 0) echo "    <option>【 재야 】</option>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
    }

    foreach(getAllNationStaticInfo() as $nation){
        echo "<option style=color:{$nation['color']}>【 {$nation['name']} 】</option>";

        $query = "select no,name,npc from general where nation='{$nation['nation']}' and level!='12' and npc<2 order by npc,binary(name)";
        $genresult = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
            elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
            else                       { $style = ""; }
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }
    echo "
</select>
<input type=submit value=등용>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender();
}

function command_23($connect, $turn, $command) {
    starter("포상", 1);
    $query = "select no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level,npc from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
국고로 장수에게 자금이나 군량을 지급합니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<select name=fourth size=1 style=color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=20>2000</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=포상>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender(1);
}

function command_24($connect, $turn, $command) {
    starter("몰수", 1);
    $query = "select no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level,npc from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
장수의 자금이나 군량을 몰수합니다.<br>
몰수한것은 국가재산으로 귀속됩니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=text-align:right;color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<select name=fourth size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=20>2000</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=몰수>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender(1);
}

function command_25($connect, $turn, $command) {
    global $_basecolor2, $_basecolor4, $images;

    starter("임관");

    $query = "select startyear,year from game limit 1";
    $result = MYDB_query($query, $connect) or Error("command_46 ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,nations from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("command_27 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    
    $query = "select nation,name,color,scout,scoutmsg,tricklimit,gennum from nation order by gennum";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    echo "
국가에 임관합니다.<br>
이미 임관/등용되었던 국가는 다시 임관할 수 없습니다.<br>
바로 군주의 위치로 이동합니다.<br>
임관할 국가를 목록에서 선택하세요.<br>
!!!는 방랑군을 포함한 랜덤임관입니다. 유니크를 기대하신다면!<br>
???는 방랑군을 제외한 랜덤임관입니다. 유니크 혜택은 없습니다.<br>
임관 금지이거나 초기 제한중인 국가는 붉은색 배경으로 표시됩니다.<br>
900px x 200px 넘는 높이를 점유할 시 통보없이 제한될 수 있습니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>
    <option value=99 style=color:white;background-color:black;>!!!</option>
    <option value=98 style=color:white;background-color:black;>???</option>";

    $scoutStr = "";
    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);

        if($nation['scoutmsg'] == "") {
            $scoutStr .= "<tr><td align=center width=100 style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</td><td width=900 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>-</td></tr>";
        } else {
            $scoutStr .= "<tr><td align=center width=100 style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</td><td width=900 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>".$nation['scoutmsg']."</td></tr>";
        }

        if($admin['year'] < $admin['startyear']+3 && $nation['gennum'] >= 10) {
            echo "
    <option value={$nation['nation']} style=color:{$nation['color']};background-color:red;>【 {$nation['name']} 】</option>";
        } elseif($nation['scout'] == 1) {
            echo "
    <option value={$nation['nation']} style=color:{$nation['color']};background-color:red;>【 {$nation['name']} 】</option>";
        } elseif(strpos($me['nations'], ",{$nation['nation']},") > 0) {
            /*
            echo "
    <option value={$nation['nation']} style=color:{$nation['color']};background-color:red; disabled>【 {$nation['name']} 】</option>";
            */
        } else {
            echo "
    <option value={$nation['nation']} style=color:{$nation['color']};>【 {$nation['name']} 】</option>";
        }
    }
    echo "
</select>
<input type=submit value=임관>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<tr><td align=center colspan=2 id=bg1>임관 권유 메세지</td></tr>
{$scoutStr}
</table>";

    ender();
}

function command_27($connect, $turn, $command) {
    starter("발령", 1);

    $query = "select no,nation,level from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("command_27 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select no,name,level,npc from general where nation='{$me['nation']}' and no!='{$me['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 아국 장수를 발령합니다.<br>
아국 도시로만 발령이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=text-align:right;color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }

    echo "
</select>
 =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=발령>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender(1);
}

function command_30($connect, $turn, $command) {
    starter("강행");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 강행합니다.<br>
최대 3칸내 도시로만 강행이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=강행>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
<input type=hidden name=turn[] value=$turn[$i]>";
}

echo "
</form>
";

printCitysName($currentcity['city'], 3);

ender();
}

function command_31($connect, $turn, $command) {
    starter("첩보");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 첩보를 실행합니다.<br>
인접도시일 경우 많은 정보를 얻을 수 있습니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=첩보>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    echo "
모든 도시가 가능하지만 많은 정보를 얻을 수 있는<br>
";

    printCitysName($currentcity['city'], 2);

    ender();
}

function command_32($connect, $turn, $command) {
    starter("화계");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 화계를 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=화계>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 2);

    ender();
}

function command_33($connect, $turn, $command) {
    starter("탈취");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 탈취를 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=탈취>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 2);

    ender();
}

function command_34($connect, $turn, $command) {
    starter("파괴");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 파괴를 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=파괴>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 2);

    ender();
}

function command_35($connect, $turn, $command) {
    starter("선동");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 선동을 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=선동>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 2);

    ender();
}

function command_36($connect, $turn, $command) {
    starter("기습");
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 기습을 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=기습>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitysName($currentcity['city'], 2);

    ender();
}

function command_43($connect, $turn, $command) {
    starter("증여");
    $query = "select no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level,npc from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
자신의 자금이나 군량을 다른 장수에게 증여합니다.<br>
장수를 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 9) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<select name=fourth size=1 style=color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=20>2000</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=증여>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_44($connect, $turn, $command) {
    starter("헌납");

    echo "
자신의 자금이나 군량을 국가 재산으로 헌납합니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=20>2000</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=헌납>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_46($connect, $turn, $command) {
    starter("건국");

    $query = "select maxnation from game limit 1";
    $result = MYDB_query($query, $connect) or Error("command_46 ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $color = GetNationColors();

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        $nationcolor[$i] = $nation['color'];
    }
    $validCount = count($color);
    //등록된 색깔 가려내기
    for($i=0; $i < count($color); $i++) {
        $valid[$i] = 1;
        for($j=0; $j < $nationcount; $j++) {
            if($color[$i] == $nationcolor[$j]) {
                $valid[$i] = 0;
                $validCount--;
            }
        }
    }
    //색깔이 다 쓰였으면 그냥 모두 허용
    if($validCount <= 0) {
        for($i=0; $i < count($color); $i++) {
            $valid[$i] = 1;
        }
    }

    if($nationcount < $admin['maxnation']) {
            echo "
현재 도시에서 나라를 세웁니다. 중, 소도시에서만 가능합니다.<br>

- 법 가 : <font color=cyan>금수입↑ 치안↑</font> <font color=magenta>인구↓ 민심↓</font><br>
- 유 가 : <font color=cyan>내정↑ 민심↑</font> <font color=magenta>쌀수입↓</font><br>
- 병 가 : <font color=cyan>기술↑ 수성↑</font> <font color=magenta>인구↓ 민심↓</font><br>
- 덕 가 : <font color=cyan>치안↑인구↑ 민심↑</font> <font color=magenta>쌀수입↓ 수성↓</font><br>
- 묵 가 : <font color=cyan>수성↑</font> <font color=magenta>기술↓</font><br>
- 도 가 : <font color=cyan>인구↑</font> <font color=magenta>기술↓ 치안↓</font><br>
- 태 평 도 : <font color=cyan>인구↑ 민심↑</font> <font color=magenta>기술↓ 수성↓</font><br>
- 오 두 미 도 : <font color=cyan>쌀수입↑ 인구↑</font> <font color=magenta>기술↓ 수성↓ 내정↓</font><br>
- 도 적 : <font color=cyan>계략↑</font> <font color=magenta>금수입↓ 치안↓ 민심↓</font><br>
- 불 가 : <font color=cyan>민심↑ 수성↑</font> <font color=magenta>금수입↓</font><br>
- 종 횡 가 : <font color=cyan>전략↑ 수성↑</font> <font color=magenta>금수입↓ 내정↓</font><br>
- 음 양 가 : <font color=cyan>내정↑ 인구↑ </font> <font color=magenta>기술↓ 전략↓</font><br>
- 명 가 : <font color=cyan>기술↑ 인구↑</font> <font color=magenta>쌀수입↓ 수성↓</font><br>

<form name=form1 action=c_double.php method=post>
국명 : <input type=text name=name size=12 maxlength=6 style=text-align:right;color:white;background-color:black>
색깔 : <select name=double size=1>";
            for($i=0; $i < count($color); $i++) {
                if($valid[$i]) {
                    echo "
    <option value={$i} style=background-color:{$color[$i]};color:".newColor($color[$i]).";>국가명</option>";
                }
            }

        echo "
</select>
성향 : <select name=third size=1>
    <option value=1 style=background-color:black;color:white;>".getNationType(1)."</option>
    <option value=2 style=background-color:black;color:white;>".getNationType(2)."</option>
    <option value=10 style=background-color:black;color:white;>".getNationType(10)."</option>
    <option value=3 style=background-color:black;color:white;>".getNationType(3)."</option>
    <option value=4 style=background-color:black;color:white;>".getNationType(4)."</option>
    <option value=5 style=background-color:black;color:white;>".getNationType(5)."</option>
    <option value=6 style=background-color:black;color:white;>".getNationType(6)."</option>
    <option value=7 style=background-color:black;color:white;>".getNationType(7)."</option>
    <option value=8 style=background-color:black;color:white;>".getNationType(8)."</option>
    <option selected value=9 style=background-color:black;color:white;>".getNationType(9)."</option>
    <option value=11 style=background-color:black;color:white;>".getNationType(11)."</option>
    <option value=12 style=background-color:black;color:white;>".getNationType(12)."</option>
    <option value=13 style=background-color:black;color:white;>".getNationType(13)."</option>
</select>
<input type=submit value=건국>
<input type=hidden name=command value=$command>";
        for($i=0; $i < sizeof($turn); $i++) {
            echo "
<input type=hidden name=turn[] value=$turn[$i]>";
        }

        echo "
</form>";
    } else {
        echo "
더 이상 건국은 불가능합니다.<br>";
    }
    ender();
}

function command_48($connect, $turn, $command) {
    starter("장비 매매");

    $query = "select no,city,gold from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("command_48 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    $city = getCity($connect, $me['city'], "secu");

    for($i=1; $i <= 6; $i++) {
        if($city['secu'] >= $i*1000) {
            $color[$i] = "white";
        } else {
            $color[$i] = "red";
        }
    }

    echo "
장비를 구입하거나 매각합니다.<br>
현재 구입 불가능한 것은 <font color=red>붉은색</font>으로 표시됩니다.<br>
현재 도시 치안 : {$city['secu']} &nbsp;&nbsp;&nbsp;현재 자금 : {$me['gold']}<br>
<form name=form1 action=c_double.php method=post>
장비 : <select name=double size=1 style=color:white;background-color:black>
    <option value=0   style=color:skyblue>_____무기매각(반값)____</option>
    <option value=1   style=color:$color[1]>".getWeapName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(1)."</option>
    <option value=2   style=color:$color[2]>".getWeapName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(2)."</option>
    <option value=3   style=color:$color[3]>".getWeapName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(3)."</option>
    <option value=4   style=color:$color[4]>".getWeapName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(4)."</option>
    <option value=5   style=color:$color[5]>".getWeapName(5)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(5)."</option>
    <option value=6   style=color:$color[6]>".getWeapName(6)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(6)."</option>
    <option value=100 style=color:skyblue>_____서적매각(반값)____</option>
    <option value=101 style=color:$color[1]>".getBookName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(1)."</option>
    <option value=102 style=color:$color[2]>".getBookName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(2)."</option>
    <option value=103 style=color:$color[3]>".getBookName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(3)."</option>
    <option value=104 style=color:$color[4]>".getBookName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(4)."</option>
    <option value=105 style=color:$color[5]>".getBookName(5)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(5)."</option>
    <option value=106 style=color:$color[6]>".getBookName(6)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(6)."</option>
    <option value=200 style=color:skyblue>_____명마매각(반값)____</option>
    <option value=201 style=color:$color[1]>".getHorseName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(1)."</option>
    <option value=202 style=color:$color[2]>".getHorseName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(2)."</option>
    <option value=203 style=color:$color[3]>".getHorseName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(3)."</option>
    <option value=204 style=color:$color[4]>".getHorseName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(4)."</option>
    <option value=205 style=color:$color[5]>".getHorseName(5)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(5)."</option>
    <option value=206 style=color:$color[6]>".getHorseName(6)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(6)."</option>
    <option value=300 style=color:skyblue>_____도구매각(반값)____</option>
    <option value=301 style=color:$color[1]>".getItemName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(1)."</option>
    <option value=302 style=color:$color[2]>".getItemName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(2)."</option>
    <option value=303 style=color:$color[3]>".getItemName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(3)."</option>
    <option value=304 style=color:$color[4]>".getItemName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(4)."</option>
    <option value=305 style=color:$color[5]>".getItemName(5)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(5)."</option>
    <option value=306 style=color:$color[6]>".getItemName(6)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(6)."</option>
</select>
<input type=submit value=거래>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_49($connect, $turn, $command) {
    starter("군량매매");

    echo "
자신의 군량을 사거나 팝니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>
    <option value=1>팜</option>
    <option value=2>삼</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=20>2000</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=거래>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_51($connect, $turn, $command) {
    starter("항복 권고", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select nation,power from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $myNation = MYDB_fetch_array($result);

    echo "
타국에게 항복을 권고합니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
권고할 국가를 목록에서 선택하세요.<br>
합병이 불가능한 국가는 <font color=red>붉은</font> 배경으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    $query = "select nation,name,power,color from nation where nation!='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);

        if($myNation['power'] / $nation['power'] <= 3 || !isClose($me['nation'], $nation['nation'])) {
            echo "<option style=color:{$nation['color']};background-color:red; value={$nation['nation']}>【 {$nation['name']} 】</option>";
        } else {
            echo "<option style=color:{$nation['color']} value={$nation['nation']}>【 {$nation['name']} 】</option>";
        }
    }
    echo "
</select>
<input type=submit value=항복권고>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
제한 조건<br>
- 인접 국가<br>
- 양국 모두 외교제한 기간 없음<br>
- 제의한 국가가 항복하는 국가보다 국력 3배 초과<br>
";
    ender(1);
}

function command_52($connect, $turn, $command) {
    starter("원조", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $mynation = getNationStaticInfo($me['nation']);
    
    echo "
타국에게 원조합니다.<br>
작위별로 금액 제한이 있습니다.<br>
　호족: 1만<br>
　군벌: 2만<br>
주자사: 3만<br>
　주목: 4만<br>
　　공: 5만<br>
　　왕: 6만<br>
　황제: 7만<br>
원조할 국가를 목록에서 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    foreach(getAllNationStaticInfo() as $nation) {

        echo "<option style=color:{$nation['color']} value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
국고 <select name=third size=1 style=text-align:right;color:white;background-color:black>
    <option value=0>0</option>
    <option value=1>1000</option>
    <option value=2>2000</option>
    <option value=3>3000</option>
    <option value=4>4000</option>
    <option value=5>5000</option>
    <option value=6>6000</option>
    <option value=7>7000</option>
    <option value=8>8000</option>
    <option value=9>9000</option>";
    for($i=1; $i <= $mynation['level']; $i++) {
        echo "<option value={$i}0>{$i}0000</option>";
    }
    echo "
</select>
병량 <select name=fourth size=1 style=text-align:right;color:white;background-color:black>
    <option value=0>0</option>
    <option value=1>1000</option>
    <option value=2>2000</option>
    <option value=3>3000</option>
    <option value=4>4000</option>
    <option value=5>5000</option>
    <option value=6>6000</option>
    <option value=7>7000</option>
    <option value=8>8000</option>
    <option value=9>9000</option>";
    for($i=1; $i <= $mynation['level']; $i++) {
        echo "<option value={$i}0>{$i}0000</option>";
    }
    echo "
</select>
<input type=submit value=원조>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_53($connect, $turn, $command) {
    starter("통합제의", 1);

    $query = "select no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("command_53 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select round(avg(power)) as power,round(avg(gennum)) as gennum from nation where level>=1";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $avgNation = MYDB_fetch_array($result);

    $query = "select round(std(power)) as power,round(std(gennum)) as gennum from nation where level>=1";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $stdNation = MYDB_fetch_array($result);
    
    $query = "select nation,power,gennum from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $myNation = MYDB_fetch_array($result);

    echo "
세력이 비슷한 국가에 통합 제의를 합니다. 제의한 국가 위주로 통합됩니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
통합이 불가능한 국가는 <font color=red>붉은</font> 배경으로 표시됩니다.<br>
        <form name=form1 action=c_double.php method=post>
대상국 : <select name=double size=1 style=color:white;background-color:black>";

    $query = "select nation,name,power,gennum,color from nation where nation!='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $cond1 = $avgNation['power']+$stdNation['power'];
    $cond2 = $avgNation['gennum']+$stdNation['gennum'];
    
    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);

        if($myNation['power']+$nation['power'] > $cond1 || $myNation['gennum']+$nation['gennum'] > $cond2 || !isClose($me['nation'], $nation['nation'])) {
            echo "<option style=color:{$nation['color']};background-color:red; value={$nation['nation']}>【 {$nation['name']} 】</option>";
        } else {
            echo "<option style=color:{$nation['color']} value={$nation['nation']}>【 {$nation['name']} 】</option>";
        }
    }

    echo "
            </select>
        통합국명 : <input type=text name=nationname size=12 maxlength=6 style=text-align:right;color:white;background-color:black>
        <input type=submit value=통합제의>
        <input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
        </form>
제한 조건<br>
- 인접 국가<br>
- 양국 모두 외교제한 없음<br>
- 양국 국력의 합이 평균+표준편차(현재 {$cond1}) 이하<br>
- 양국 장수수의 합이 평균+표준편차(현재 {$cond2}) 이하<br>
";
    ender(1);
}

function command_54($connect, $turn, $command) {
    starter("선양");
    $query = "select no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
군주의 자리를 다른 장수에게 물려줍니다.<br>
장수를 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<input type=submit value=선양>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_61($connect, $turn, $command) {
    starter("불가침", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
타국에게 불가침을 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
비고는 불가침시 세부 조건등을 지정하는 곳입니다.(최대 45자) <font color=magenta>※유저 편의를 위한 것일뿐 시스템적으로 보장하지는 않습니다.</font><br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "red"; break;
            case 2: $color = "black"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
기간 <select name=third size=1 style=text-align:right;color:white;background-color:black>
    <option value=1 > 1</option>
    <option value=2 > 2</option>
    <option value=3 > 3</option>
    <option value=4 > 4</option>
    <option value=5 > 5</option>
    <option value=6 > 6</option>
    <option value=7 > 7</option>
    <option value=8 > 8</option>
    <option value=9 > 9</option>
    <option value=10>10</option>
    <option value=11>11</option>
    <option value=12>12</option>
    <option value=13>13</option>
    <option value=14>14</option>
    <option value=15>15</option>
    <option value=16>16</option>
    <option value=17>17</option>
    <option value=18>18</option>
    <option value=19>19</option>
    <option value=20>20</option>
</select>
년<br>
비고 : <input type=text name=note size=45 maxlength=45 style=text-align:left;color:white;background-color:black>
<input type=submit value=불가침>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_62($connect, $turn, $command) {
    starter("선전포고", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
타국에게 선전 포고합니다.<br>
선전 포고할 국가를 목록에서 선택하세요.<br>
고립되지 않은 아국 도시에서 인접한 국가에 선포 가능합니다.<br>
초반제한 해제 2년전부터 선포가 가능합니다. (체섭기준 181년 1월부터 가능)<br>
배경색은 현재 선포가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중(역시 불가)인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }
        //합병중 국가는 안됨
        $query = "select state from diplomacy where me='{$nation['nation']}' and (state='3' or state='5')";
        $tempresult = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
        $anycount = MYDB_num_rows($tempresult);
        if($anycount != 0 && $diplomacy[$nation['nation']] != 7) {
            $diplomacy[$nation['nation']] = 3;
        }
        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "red"; break;
            case 2: $color = "black"; break;
            case 3: $color = "red"; break;
            case 4: $color = "black"; break;
            case 5: $color = "red"; break;
            case 6: $color = "black"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=선전포고>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_63($connect, $turn, $command) {
    starter("종전", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
전쟁중인 국가에 종전을 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "black"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "red"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=종전제의>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_64($connect, $turn, $command) {
    starter("파기", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
불가침중인 국가에 조약 파기를 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중(제의 가능)인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "red"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=파기제의>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_65($connect, $turn, $command) {
    starter("초토화", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시를 초토화 시킵니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=초토화>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_66($connect, $turn, $command) {
    starter("천도", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시로 천도합니다.<br>
현재 수도에서 인접한 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=천도>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_67($connect, $turn, $command) {
    starter("증축", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시를 증축합니다.<br>
현재 수도만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=증축>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_68($connect, $turn, $command) {
    starter("감축", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시를 감축합니다.<br>
현재 수도만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=감축>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_72($connect, $turn, $command) {
    starter("백성동원", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시에 백성동원을 발동합니다.<br>
아국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=백성동원>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_73($connect, $turn, $command) {
    starter("수몰", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시에 수몰을 발동합니다.<br>
전쟁중인 상대국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=수몰>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_74($connect, $turn, $command) {
    starter("허보", 1);
    $query = "select city from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시에 허보를 발동합니다.<br>
선포, 전쟁중인 상대국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    OptionsForCitys();

    echo "
</select>
<input type=submit value=허보>
<input type=hidden name=command value=$command>";

    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_75($connect, $turn, $command) {
    starter("피장파장", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
선택된 국가에 피장파장을 발동합니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 피장파장 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "black"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=피장파장>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_77($connect, $turn, $command) {
    starter("이호경식", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
선택된 국가에 이호경식을 발동합니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 이호경식 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "black"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=이호경식>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_78($connect, $turn, $command) {
    starter("급습", 1);

    $query = "select nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
선택된 국가에 급습을 발동합니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 급습 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style=background-color:$color;color:{$nation['color']}; value={$nation['nation']}>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=급습>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_81($connect, $turn, $command) {
    starter("국기변경", 1);

    $color = GetNationColors();

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }

        $nationcolor[$i] = $nation['color'];
    }
    $validCount = count($color);
    //등록된 색깔 가려내기
    for($i=0; $i < count($color); $i++) {
        $valid[$i] = 1;
        for($j=0; $j < $nationcount; $j++) {
            if($color[$i] == $nationcolor[$j]) {
                $valid[$i] = 0;
                $validCount--;
            }
        }
    }
    //색깔이 다 쓰였으면 그냥 모두 허용
    if($validCount <= 0) {
        for($i=0; $i < count($color); $i++) {
            $valid[$i] = 1;
        }
    }

    echo "
국기를 변경합니다. 단 1회 가능합니다.<br>
<form name=form1 action=c_double.php method=post>
색깔 : <select name=double size=1>";
            for($i=0; $i < count($color); $i++) {
                if($valid[$i]) {
                    echo "
    <option value={$i} style=background-color:{$color[$i]};color:".newColor($color[$i]).";>국가명</option>";
                }
            }
    echo "
</select>
<input type=submit value=국기변경>
<input type=hidden name=command value=$command>";
    for($i=0; $i < sizeof($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}
