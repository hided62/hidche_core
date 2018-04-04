<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();
$connect=$db->get();

increaseRefresh("세력정보", 1);

$query = "select no,nation,level from general where owner='{$session->userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['level'] == 0) {
    echo "재야입니다.";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>세력정보</title>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>세 력 정 보<br><?=backButton()?></td></tr>
</table>
<br>
<?php
$query = "select nation from general where owner='{$session->userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select nation,gennum,power,rate,bill,type,gold,rice,color,name,level,tech,history,capital from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);   //국가정보

$query = "select city,name,pop,pop2 from city where nation='{$nation['nation']}'"; // 도시 이름 목록
$cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$citycount = MYDB_num_rows($cityresult);

for($j=0; $j < $citycount; $j++) {
    $city = MYDB_fetch_array($cityresult);
    if($city['city'] == $nation['capital']) { $cityname[$j] = "<font color=cyan>[{$city['name']}]</font>"; }
    else { $cityname[$j] = $city['name']; }
    $totalpop += $city['pop'];
    $maxpop += $city['pop2'];
}

$query = "select sum(crew) as totcrew,sum(leader)*100 as maxcrew from general where nation='{$nation['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$general = MYDB_fetch_array($result);

$query = "select gold_rate,rice_rate from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);
// 금 수지
$deadIncome = getDeadIncome($nation['nation'], $nation['type'], $admin['gold_rate']);

$goldincomeList  = getGoldIncome($nation['nation'], $nation['rate'], $admin['gold_rate'], $nation['type']);
$goldincome  = $goldincomeList[0] + $goldincomeList[1] + $deadIncome;
$goldoutcome = getGoldOutcome($nation['nation'], $nation['bill']);
$riceincomeList = getRiceIncome($nation['nation'], $nation['rate'], $admin['rice_rate'], $nation['type']);
$riceincome  = $riceincomeList[0] + $riceincomeList[1];
$riceoutcome = getRiceOutcome($nation['nation'], $nation['bill']);


$budgetgold = $nation['gold'] + $goldincome - $goldoutcome + $deadIncome;
$budgetrice = $nation['rice'] + $riceincome - $riceoutcome;
$budgetgolddiff = $goldincome - $goldoutcome + $deadIncome;
$budgetricediff = $riceincome - $riceoutcome;
if($budgetgolddiff > 0) { $budgetgolddiff = "+{$budgetgolddiff}"; }
else { $budgetgolddiff = "$budgetgolddiff"; }
if($budgetricediff > 0) { $budgetricediff = "+{$budgetricediff}"; }
else { $budgetricediff = "$budgetricediff"; }

echo "
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr>
        <td colspan=8 align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}>【 ";echo $me['nation']==0?"공 백 지":"{$nation['name']}";echo " 】</td>
    </tr>
    <tr>
        <td width=98 align=center id=bg1>총주민</td>
        <td width=198 align=center>{$totalpop}/{$maxpop}</td>
        <td width=98 align=center id=bg1>총병사</td>
        <td width=198 align=center>{$general['totcrew']}/{$general['maxcrew']}</td>
        <td width=98 align=center id=bg1>국 력</td>
        <td width=298 align=center colspan=3>{$nation['power']}</td>
    </tr>
    <tr>
        <td align=center id=bg1>국 고</td>
        <td align=center>"; echo $nation['gold']==0?"-":"{$nation['gold']}"; echo "</td>
        <td align=center id=bg1>병 량</td>
        <td align=center>"; echo $nation['rice']==0?"-":"{$nation['rice']}"; echo "</td>
        <td align=center id=bg1>세 율</td>
        <td align=center colspan=3>"; echo $me['nation']==0?"해당 없음":"{$nation['rate']} %"; echo "</td>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1>세금/단기</td>
        <td align=center>+$goldincomeList[0] / +$deadIncome</td>
        <td align=center id=bg1>세곡/둔전</td>
        <td align=center>+$riceincomeList[0] / +$riceincomeList[1]</td>
        <td align=center id=bg1>지급율</td>
        <td align=center colspan=3>"; echo $me['nation']==0?"해당 없음":"{$nation['bill']} %"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1>수입/지출</td>
        <td align=center>+$goldincome / -$goldoutcome</td>
        <td align=center id=bg1>수입/지출</td>
        <td align=center>+$riceincome / -$riceoutcome</td>
        <td align=center id=bg1>속 령</td>
        <td width=98 align=center>$citycount</td>
        <td width=98 align=center id=bg1>장 수</td>
        <td width=98 align=center>{$nation['gennum']}</td>
    </tr>
    <tr>
        <td align=center id=bg1>국고 예산</td>
        <td align=center>{$budgetgold} ({$budgetgolddiff})</td>
        <td align=center id=bg1>병량 예산</td>
        <td align=center>{$budgetrice} ({$budgetricediff})</td>
        <td align=center id=bg1>기술력</td>
        <td align=center>{$nation['tech']}</td>
        <td align=center id=bg1>작 위</td>
        <td align=center>".getNationLevel($nation['level'])."</td>
    </tr>
    <tr>
        <td align=center valign=top id=bg1> 속령일람 :</td>
        <td colspan=7>";
for($j=0; $j < $citycount; $j++) {
    echo "$cityname[$j], ";
}
echo"
        </td>
    </tr>
    <tr>
        <td align=center valign=top id=bg1>국가열전</td>
        <td colspan=7 id=bg0>".ConvertLog($nation['history'])."</td>
    </tr>
</table>
<br>";

?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>

</html>

