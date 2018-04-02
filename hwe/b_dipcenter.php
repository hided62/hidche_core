<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLoginWithGeneralID();
$connect = dbConn();
increaseRefresh("내무부", 1);

$query = "select conlimit from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,nation,level,con,turntime,belong from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

if($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

if($me['level'] >= 5) { $btn = "submit"; $read = ""; }
else { $btn = "hidden"; $read = "readonly"; }

?>
<!DOCTYPE html>
<html>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>내무부</title>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>내 무 부<br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=9 align=center bgcolor=blue>외 교 관 계</td></tr>
    <tr>
        <td width=100 align=center id=bg1>국 가 명</td>
        <td width=50  align=center id=bg1>국력</td>
        <td width=40  align=center id=bg1>장수</td>
        <td width=40  align=center id=bg1>속령</td>
        <td width=80  align=center id=bg1>상태</td>
        <td width=60  align=center id=bg1>기간</td>
        <td width=100 align=center id=bg1>종 료 시 점</td>
        <td align=center id=bg1>비 고</td>
    </tr>
<?php
$query = "select year,month from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select nation,name,color,power,gennum from nation order by power desc";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nationcount = MYDB_num_rows($result);
for($i=0; $i < $nationcount; $i++) {
    $nation = MYDB_fetch_array($result);

    // 아국표시
    if($nation['nation'] == $me['nation']) {
        //속령수
        $query = "select city from city where nation='{$nation['nation']}'";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $citycount = MYDB_num_rows($result2);
        echo "
    <tr>
        <td align=center style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</td>
        <td align=center>{$nation['power']}</td>
        <td align=center>{$nation['gennum']}</td>
        <td align=center>$citycount</td>
        <td align=center>-</td>
        <td align=center>-</td>
        <td align=center>-</td>
        <td align=left style=font-size:7px;>-</td>
    </tr>";

        continue;
    }

    $query = "select state,term,fixed,reserved,showing from diplomacy where me='{$me['nation']}' and you='{$nation['nation']}'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result2);

    $query = "select reserved,showing from diplomacy where you='{$me['nation']}' and me='{$nation['nation']}'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip2 = MYDB_fetch_array($result2);
    //속령수
    $query = "select city from city where nation='{$nation['nation']}'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result2);
    switch($dip['state']) {
        case 0: $state = "<font color=red>교 전</font>"; break;
        case 1: $state = "<font color=magenta>선포중</font>"; break;
        case 2: $state = "통 상"; break;
        case 3: $state = "<font color=cyan>통합수락중</font>"; break;
        case 4: $state = "<font color=cyan>통합제의중</font>"; break;
        case 5: $state = "<font color=cyan>합병수락중</font>"; break;
        case 6: $state = "<font color=cyan>합병제의중</font>"; break;
        case 7: $state = "<font color=green>불가침</font>"; break;
    }

    $term = $admin['year'] * 12 + $admin['month'] + $dip['term'];
    $year = floor($term / 12);
    $month = $term % 12;

    if($month == 0) {
        $month = 12;
        $year--;
    }

    $date = date('Y-m-d H:i:s');
    $note = "";
    if($dip['fixed'] != "") {
        if($dip['state'] == 7) {
            $note .= $dip['fixed'];
        } else {
            $note .= "<font color=gray>{$dip['fixed']}</font>";
        }
        if($dip['reserved'] != "" || $dip2['reserved'] != "") {
            $note .= "<br>";
        }
    }
    if($dip['showing'] >= $date) {
        if($dip['reserved'] != "") {
            $note .= "<font color=skyblue>아국측 제의</font>: ".$dip['reserved'];
            if($dip2['reserved'] != "") {
                $note .= "<br>";
            }
        }
    }
    if($dip2['showing'] >= $date) {
        if($dip2['reserved'] != "") {
            $note .= "<font color=limegreen>상대측 제의</font>: ".$dip2['reserved'];
        }
    }
    if($note == "") { $note = "&nbsp;"; }

    echo "
    <tr>
        <td align=center style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</td>
        <td align=center>{$nation['power']}</td>
        <td align=center>{$nation['gennum']}</td>
        <td align=center>$citycount</td>
        <td align=center>$state</td>";
    if($dip['term'] != 0) {
        echo"
        <td align=center>{$dip['term']} 개월</td>
        <td align=center>{$year}年 {$month}月</td>";
    } else {
        echo"
        <td align=center>-</td>
        <td align=center>-</td>";
    }
    echo "
        <td align=left style=font-size:7px;>{$note}</td>
    </tr>";
}
echo "
</table>
";

$query = "select nation,name,color,type,msg,gold,rice,bill,rate,scout,war,scoutmsg,secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

$query = "select gold_rate,rice_rate from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);
// 금 수지
$deadIncome = getDeadIncome($connect, $nation['nation'], $nation['type'], $admin['gold_rate']);

$goldincomeList  = getGoldIncome($connect, $nation['nation'], $nation['rate'], $admin['gold_rate'], $nation['type']);
$goldincome  = $goldincomeList[0] + $goldincomeList[1] + $deadIncome;
$goldoutcome = getGoldOutcome($connect, $nation['nation'], $nation['bill']);
$riceincomeList = getRiceIncome($connect, $nation['nation'], $nation['rate'], $admin['rice_rate'], $nation['type']);
$riceincome  = $riceincomeList[0] + $riceincomeList[1];
$riceoutcome = getRiceOutcome($connect, $nation['nation'], $nation['bill']);


$budgetgold = $nation['gold'] + $goldincome - $goldoutcome + $deadIncome;
$budgetrice = $nation['rice'] + $riceincome - $riceoutcome;
$budgetgolddiff = $goldincome - $goldoutcome + $deadIncome;
$budgetricediff = $riceincome - $riceoutcome;
if($budgetgolddiff > 0) { $budgetgolddiff = "+{$budgetgolddiff}"; }
else { $budgetgolddiff = "$budgetgolddiff"; }
if($budgetricediff > 0) { $budgetricediff = "+{$budgetricediff}"; }
else { $budgetricediff = "$budgetricediff"; }

?>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<form name=form1 method=post action=c_dipcenter.php>
    <tr><td colspan=2 height=10></td></tr>
    <tr><td colspan=2 align=center bgcolor=orange>국 가 방 침 & 임관 권유 메세지</td></tr>
    <tr><td colspan=2 id=bg1>국가 방침 <input type=text <?=$read;?> maxlength=500 style=color:white;background-color:black;width:830; name=msg value='<?=$nation['msg'];?>'><input type=<?=$btn;?> name=btn value=국가방침></td></tr>
    <tr><td colspan=2 id=bg1>임관 권유 <input type=text <?=$read;?> maxlength=500 style=color:white;background-color:black;width:830; name=scoutmsg value='<?=$nation['scoutmsg'];?>'><input type=<?=$btn;?> name=btn value=임관권유></td></tr>
    <tr><td colspan=2>900 x 200px 넘는 크기를 점유할 시 통보없이 제한될 수 있습니다.</td></tr>
    <tr><td colspan=2 height=10></td></tr>
    <tr><td colspan=2 align=center bgcolor=green>예 산 & 정 책</td></tr>
    <tr>
        <td colspan=2>
            <table width=998 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<?php
echo "
                <tr>
                    <td colspan=2 align=center id=bg1>자금 예산</td>
                    <td colspan=2 align=center id=bg1>병량 예산</td>
                </tr>
                <tr>
                    <td width=248 align=right id=bg1>현 재&nbsp;&nbsp;&nbsp;</td>
                    <td width=248 align=center>{$nation['gold']}</td>
                    <td width=248 align=right id=bg1>현 재&nbsp;&nbsp;&nbsp;</td>
                    <td width=248 align=center>{$nation['rice']}</td>
                </tr>
                <tr>
                    <td align=right id=bg1>단기수입&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+$deadIncome</td>
                    <td align=right id=bg1>둔전수입&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+$riceincomeList[1]</td>
                </tr>
                <tr>
                    <td align=right id=bg1>세 금&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+$goldincomeList[0]</td>
                    <td align=right id=bg1>세 곡&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+$riceincomeList[0]</td>
                </tr>
                <tr>
                    <td align=right id=bg1>수입 / 지출&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+$goldincome / -$goldoutcome</td>
                    <td align=right id=bg1>수입 / 지출&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+$riceincome / -$riceoutcome</td>
                </tr>
                <tr>
                    <td align=right id=bg1>국고 예산&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>{$budgetgold} ({$budgetgolddiff})</td>
                    <td align=right id=bg1>병량 예산&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>{$budgetrice} ({$budgetricediff})</td>
                </tr>";
?>
                <tr>
                    <td align=right id=bg1>세율 (5 ~ 30%)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read;?> name=rate style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['rate']?>>% <input type=<?=$btn;?> name=btn value=세율></td>
                    <td align=right id=bg1>봉급 지급율 (20 ~ 200%)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read;?> name=bill style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['bill']?>>% <input type=<?=$btn;?> name=btn value=지급율></td>
                </tr>
                <tr>
                    <td align=right id=bg1>기밀 권한 (1 ~ 99년)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read;?> name=secretlimit style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['secretlimit']?>>년 <input type=<?=$btn;?> name=btn value=기밀권한></td>
                    <td align=right id=bg1>임관&전쟁 변경 가능</td>
                    <td align=center>무제한</td>
                </tr>
                <tr>
                    <td colspan=4 align=center>
<?php
if($nation['scout'] == 0) {
    echo "
    <input type=$btn name=btn value='임관 금지'>";
} else {
    echo "
    <input type=$btn name=btn value='임관 허가'>";
}

if($nation['war'] == 0) {
    echo "
    <input type=$btn name=btn value='전쟁 금지'>";
} else {
    echo "
    <input type=$btn name=btn value='전쟁 허가'>";
}
?>
                    </td>
                </tr>
            </table>
    <tr><td colspan=2>기밀 권한이란, 암행부를 열람할 수 있는 일반 장수의 최소 사관 년수를 의미합니다.</td></tr>
    <tr><td colspan=2 height=10></td></tr>
</form>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
