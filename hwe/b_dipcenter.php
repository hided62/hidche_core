<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("내무부", 1);

$query = "select no,nation,level,con,turntime,belong from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$query = "select secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$nation = MYDB_fetch_array($result);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

if ($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

if ($me['level'] >= 5) {
    $btn = "submit";
    $read = "";
} else {
    $btn = "hidden";
    $read = "readonly";
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 내무부</title>
<script>
var editable = <?=($me['level']>=5?'true':'false')?>;
</script>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../e_lib/summernote/summernote-bs4.min.js')?>
<?=WebUtil::printJS('../e_lib/summernote/lang/summernote-ko-KR.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/dipcenter.js')?>

<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../e_lib/summernote/summernote-bs4.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/dipcenter.css')?>
<link href="https://fonts.googleapis.com/css?family=Nanum+Gothic|Nanum+Myeongjo|Nanum+Pen+Script" rel="stylesheet">
</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>내 무 부<br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=9 align=center bgcolor=blue>외 교 관 계</td></tr>
    <tr>
        <td width=130 align=center class=bg1>국 가 명</td>
        <td width=50  align=center class=bg1>국력</td>
        <td width=40  align=center class=bg1>장수</td>
        <td width=40  align=center class=bg1>속령</td>
        <td width=80  align=center class=bg1>상태</td>
        <td width=60  align=center class=bg1>기간</td>
        <td width=100 align=center class=bg1>종 료 시 점</td>
        <td align=center class=bg1>비 고</td>
    </tr>
<?php
$admin = $gameStor->getValues(['year','month']);

$query = "select nation,name,color,power,gennum from nation order by power desc";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$nationcount = MYDB_num_rows($result);
for ($i=0; $i < $nationcount; $i++) {
    $nation = MYDB_fetch_array($result);

    // 아국표시
    if ($nation['nation'] == $me['nation']) {
        //속령수
        $query = "select city from city where nation='{$nation['nation']}'";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
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
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $dip = MYDB_fetch_array($result2);

    $query = "select reserved,showing from diplomacy where you='{$me['nation']}' and me='{$nation['nation']}'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $dip2 = MYDB_fetch_array($result2);
    //속령수
    $query = "select city from city where nation='{$nation['nation']}'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $citycount = MYDB_num_rows($result2);
    switch ($dip['state']) {
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
    $year = intdiv($term, 12);
    $month = $term % 12;

    if ($month == 0) {
        $month = 12;
        $year--;
    }

    $date = date('Y-m-d H:i:s');
    $note = "";
    if ($dip['fixed'] != "") {
        if ($dip['state'] == 7) {
            $note .= $dip['fixed'];
        } else {
            $note .= "<font color=gray>{$dip['fixed']}</font>";
        }
        if ($dip['reserved'] != "" || $dip2['reserved'] != "") {
            $note .= "<br>";
        }
    }
    if ($dip['showing'] >= $date) {
        if ($dip['reserved'] != "") {
            $note .= "<font color=skyblue>아국측 제의</font>: ".$dip['reserved'];
            if ($dip2['reserved'] != "") {
                $note .= "<br>";
            }
        }
    }
    if ($dip2['showing'] >= $date) {
        if ($dip2['reserved'] != "") {
            $note .= "<font color=limegreen>상대측 제의</font>: ".$dip2['reserved'];
        }
    }
    if ($note == "") {
        $note = "&nbsp;";
    }

    echo "
    <tr>
        <td align=center style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</td>
        <td align=center>{$nation['power']}</td>
        <td align=center>{$nation['gennum']}</td>
        <td align=center>$citycount</td>
        <td align=center>$state</td>";
    if ($dip['term'] != 0) {
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
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$nation = MYDB_fetch_array($result);

$admin = $gameStor->getValues(['gold_rate','rice_rate']);
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
if ($budgetgolddiff > 0) {
    $budgetgolddiff = "+{$budgetgolddiff}";
} else {
    $budgetgolddiff = "$budgetgolddiff";
}
if ($budgetricediff > 0) {
    $budgetricediff = "+{$budgetricediff}";
} else {
    $budgetricediff = "$budgetricediff";
}

?>
<table align=center width=1000 class='tb_layout bg0'>
<form name=form1 method=post action=c_dipcenter.php>
    <tr><td colspan=2 height=10></td></tr>
    <tr><td colspan=2 align=center bgcolor=orange>국 가 방 침 &amp; 임관 권유 메시지</td></tr>
    <tr><td colspan='2'><div id='noticeForm'>
        <div class='bg1' style="display: flex; justify-content: space-around">
            <div style='flex: 1 1 auto;'>
                국가 방침
            </div>
            <div>
                <input type='submit' class='submit' name=btn value='국가방침 수정'><input type='button' class='cancel_edit' value='취소'>
            </div>
        </div>
        <textarea type=hidden class='input_form' style='display:none;' name=msg><?=$nation['msg']?></textarea>
        <div class='edit_form viewer'></div>
    </div></td></tr>
    <tr><td colspan='2'><div id='scoutMsgForm'>
        <div class='bg1' style="display: flex; justify-content: space-around">
            <div style='flex: 1 1 auto;'>
                임관 권유
            </div>
            <div>
                <input type='submit' class='submit' name=btn value='임관 권유문 수정'><input type='button' class='cancel_edit' value='취소'>
            </div>
        </div>
        <div style='border-bottom:solid gray 0.5px;'>870px x 200px를 넘어서는 내용은 표시되지 않습니다.</div>
        <textarea type=hidden class='input_form' style='display:none;' name=scoutmsg><?=$nation['scoutmsg']?></textarea>
        <div style="width:870px;margin-left:auto;">
            <div class='edit_form viewer'></div>
        </div>
        </div>
        
    </div></td></tr>
    <tr><td colspan=2 align=center bgcolor=green>예 산 &amp; 정 책</td></tr>
    <tr>
        <td colspan=2>
            <table width=998 class='tb_layout bg0'>
                <tr>
                    <td colspan=2 align=center class=bg1>자금 예산</td>
                    <td colspan=2 align=center class=bg1>병량 예산</td>
                </tr>
                <tr>
                    <td width=248 align=right class=bg1>현 재&nbsp;&nbsp;&nbsp;</td>
                    <td width=248 align=center><?=$nation['gold']?></td>
                    <td width=248 align=right class=bg1>현 재&nbsp;&nbsp;&nbsp;</td>
                    <td width=248 align=center><?=$nation['rice']?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>단기수입&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=$deadIncome?></td>
                    <td align=right class=bg1>둔전수입&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=$riceincomeList[1]?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>세 금&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=$goldincomeList[0]?></td>
                    <td align=right class=bg1>세 곡&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=$riceincomeList[0]?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>수입 / 지출&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=$goldincome?> / -<?=$goldoutcome?></td>
                    <td align=right class=bg1>수입 / 지출&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=$riceincome?> / -<?=$riceoutcome?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>국고 예산&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><?=$budgetgold?> (<?=$budgetgolddiff?>)</td>
                    <td align=right class=bg1>병량 예산&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><?=$budgetrice?> (<?=$budgetricediff?>)</td>
                </tr>
                <tr>
                    <td align=right class=bg1>세율 (5 ~ 30%)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read?> name=rate style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['rate']?>>% <input type=<?=$btn?> name=btn value=세율></td>
                    <td align=right class=bg1>봉급 지급율 (20 ~ 200%)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read?> name=bill style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['bill']?>>% <input type=<?=$btn?> name=btn value=지급율></td>
                </tr>
                <tr>
                    <td align=right class=bg1>기밀 권한 (1 ~ 99년)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read?> name=secretlimit style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['secretlimit']?>>년 <input type=<?=$btn?> name=btn value=기밀권한></td>
                    <td align=right class=bg1>임관&amp;전쟁 변경 가능</td>
                    <td align=center>무제한</td>
                </tr>
                <tr>
                    <td colspan=4 align=center>
<?php
if ($nation['scout'] == 0) {
    echo "
    <input type=$btn name=btn value='임관 금지'>";
} else {
    echo "
    <input type=$btn name=btn value='임관 허가'>";
}

if ($nation['war'] == 0) {
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
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
