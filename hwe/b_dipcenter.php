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

$me = $db->queryFirstRow('SELECT no, nation, level, con, turntime, belong, permission, penalty FROM general WHERE owner=%i', $userID);

$nationID = $me['nation'];
$nation = $db->queryFirstRow('SELECT nation,name,color,type,gold,rice,bill,rate,scout,war,secretlimit,msg,scoutmsg FROM nation WHERE nation = %i', $nationID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if($permission < 0){
    echo '국가에 소속되어있지 않습니다.';
    die();
}
else if ($permission < 1) {
    echo "권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.";
    die();
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
var editable = <?=(($me['level']>=5||$permission==4)?'true':'false')?>;
var nationMsg = <?=Json::encode($nation['msg']??'')?>;
var scoutmsg = <?=Json::encode($nation['scoutmsg']??'')?>;
</script>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../e_lib/summernote/summernote-bs4.min.js')?>
<?=WebUtil::printJS('../e_lib/summernote/lang/summernote-ko-KR.js')?>
<?=WebUtil::printJS('../e_lib/summernote/plugin/image-sammo/summernote-image-flip.js')?>
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
    </tr>
<?php
$admin = $gameStor->getValues(['year','month']);

$cityCntList = Util::convertPairArrayToDict($db->queryAllLists('SELECT nation, count(city) FROM city GROUP BY nation'));
$dipStateList = Util::convertArrayToDict($db->query('SELECT you,state,term FROM diplomacy WHERE me = %i', $nationID), 'you');


foreach(getAllNationStaticInfo() as $staticNation):
    //속령수
    $staticNationID = $staticNation['nation'];
    $cityCnt = $cityCntList[$staticNation['nation']];

    $dipStateText = '-';
    $dipTermText = '-';
    $dipEndDateText = '-';
    if($staticNationID !== $nationID){
        $diplomacyState = $dipStateList[$staticNationID];

        $dipStateText = [
            0 => "<font color=red>교 전</font>",
            1 => "<font color=magenta>선포중</font>",
            2 => "통 상",
            3 => "<font color=cyan>통합수락중</font>",
            4 => "<font color=cyan>통합제의중</font>",
            5 => "<font color=cyan>합병수락중</font>",
            6 => "<font color=cyan>합병제의중</font>",
            7 => "<font color=green>불가침</font>",    
        ][$diplomacyState['state']];

        if($diplomacyState['term']){
            $dipEndMonth = $admin['month'] + $diplomacyState['term'] - 1;
            $dipEndYear = $admin['year'] + intdiv($dipEndMonth, 12);
            $dipEndMonth = $dipEndMonth % 12 + 1;
            
            $dipTermText = $diplomacyState['term'].'개월';
            $dipEndDateText = "{$dipEndYear}年 {$dipEndMonth}月";
        }
    }
?>

<tr>
    <td class='center' style='color:<?=newColor($staticNation['color'])?>;background-color:<?=$staticNation['color']?>'><?=$staticNation['name']?></td>
    <td class='center'><?=$staticNation['power']?></td>
    <td class='center'><?=$staticNation['gennum']?></td>
    <td class='center'><?=$cityCnt?></td>
    <td class='center'><?=$dipStateText?></td>
    <td class='center'><?=$dipTermText?></td>
    <td class='center'><?=$dipEndDateText?></td>
</tr>
<?php endforeach; ?>
</table>

<?php
// 수입 연산
$cityList = $db->query('SELECT * FROM city WHERE nation=%i', $nationID);
$dedicationList = $db->query('SELECT dedication FROM general WHERE nation=%i AND npc!=5', $nationID);

$goldIncome  = getGoldIncome($nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
$warIncome  = getWarGoldIncome($nation['type'], $cityList);
$totalGoldIncome = $goldIncome + $warIncome;

$riceIncome = getRiceIncome($nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
$wallIncome = getWallIncome($nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
$totalRiceIncome = $riceIncome + $wallIncome;

$outcome = getOutcome($nation['bill'], $dedicationList);

$budgetgold = $nation['gold'] + $totalGoldIncome - $outcome;
$budgetrice = $nation['rice'] + $totalRiceIncome - $outcome;
$budgetgolddiff = $totalGoldIncome - $outcome;
$budgetricediff = $totalRiceIncome - $outcome;

if ($budgetgolddiff > 0) {
    $budgetgolddiff = '+'.number_format($budgetgolddiff);
} else {
    $budgetgolddiff = number_format($budgetgolddiff);
}
if ($budgetricediff > 0) {
    $budgetricediff = '+'.number_format($budgetricediff);
} else {
    $budgetricediff = number_format($budgetricediff);
}

?>
<table width=1000 class='tb_layout bg0 center'>
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
        <input type='hidden' class='input_form' name='msg' data-global='nationMsg'>
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
        <input type='hidden' class='input_form' name='scoutmsg' data-global='scoutmsg'>
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
                    <td width=248 align=center><?=number_format($nation['gold'])?></td>
                    <td width=248 align=right class=bg1>현 재&nbsp;&nbsp;&nbsp;</td>
                    <td width=248 align=center><?=number_format($nation['rice'])?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>단기수입&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=number_format($warIncome)?></td>
                    <td align=right class=bg1>둔전수입&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=number_format($wallIncome)?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>세 금&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=number_format($goldIncome)?></td>
                    <td align=right class=bg1>세 곡&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=number_format($riceIncome)?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>수입 / 지출&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=number_format($totalGoldIncome)?> / -<?=number_format($outcome)?></td>
                    <td align=right class=bg1>수입 / 지출&nbsp;&nbsp;&nbsp;</td>
                    <td align=center>+<?=number_format($totalRiceIncome)?> / -<?=number_format($outcome)?></td>
                </tr>
                <tr>
                    <td align=right class=bg1>국고 예산&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><?=number_format($budgetgold)?> (<?=$budgetgolddiff?>)</td>
                    <td align=right class=bg1>병량 예산&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><?=number_format($budgetrice)?> (<?=$budgetricediff?>)</td>
                </tr>
                <tr>
                    <td align=right class=bg1>세율 (5 ~ 30%)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read?> name=rate style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['rate']?>>% <input type=<?=$btn?> name=btn value=세율></td>
                    <td align=right class=bg1>봉급 지급률 (20 ~ 200%)&nbsp;&nbsp;&nbsp;</td>
                    <td align=center><input type=text <?=$read?> name=bill style=text-align:right;color:white;background-color:black; size=3 maxlength=3 value=<?=$nation['bill']?>>% <input type=<?=$btn?> name=btn value=지급률></td>
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
