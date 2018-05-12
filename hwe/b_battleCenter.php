<?php
namespace sammo;

include "lib.php";
include "func.php";

$v = new Validator($_POST + $_GET);
$v->rule('required', 'gen')
->rule('integer', 'gen');

$btn = Util::getReq('btn');
$gen = Util::getReq('gen', 'int', 0);
$type = Util::getReq('type', 'int', 0);

if ($type < 0 || $type > 3) {
    $type = 0;
}

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("감찰부", 2);
//전투 추진을 위해 갱신
checkTurn();
$gameStor->resetCache();

$query = "select nation from general where no='$gen'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$general = MYDB_fetch_array($result);

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

//재야인 경우
$meLevel = $me['level'];
if ($meLevel == 0 || ($meLevel == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

//잘못된 접근
if ($general['nation'] != $me['nation']) {
    $gen = 0;
}

if ($btn == '정렬하기') {
    $gen = 0;
}

$sel = [];
$sel[$type] = "selected";

?>
<!DOCTYPE html>
<html>

<head>
<title><?=UniqueConst::$serverName?>: 감찰부</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>감 찰 부<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0]??''?> value=0>최근턴</option>
            <option <?=$sel[1]??''?> value=1>최근전투</option>
            <option <?=$sel[2]??''?> value=2>장수명</option>
            <option <?=$sel[3]??''?> value=3>전투수</option>
        </select>
        <input type=submit name=btn value='정렬하기'>
        대상장수 :
        <select name=gen size=1>
<?php
switch ($type) {
    default:
    case 0: $query = "select no,name,npc from general where nation='{$me['nation']}' order by turntime desc"; break;
    case 1: $query = "select no,name,npc from general where nation='{$me['nation']}' order by recwar desc"; break;
    case 2: $query = "select no,name,npc from general where nation='{$me['nation']}' order by npc,binary(name)"; break;
    case 3: $query = "select no,name,npc from general where nation='{$me['nation']}' order by warnum desc"; break;
}
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$gencount = MYDB_num_rows($result);
$npc = 0;
for ($i=0; $i < $gencount; $i++) {
    $general = MYDB_fetch_array($result);
    // 선택 없으면 맨 처음 장수
    if ($gen == 0) {
        $gen = $general['no'];
    }
	if($gen == $general['no']){
		$npc = $general['npc'];
	}
    if ($gen == $general['no']) {
        echo "
            <option selected value={$general['no']}>{$general['name']}</option>";
    } else {
        echo "
            <option value={$general['no']}>{$general['name']}</option>";
    }
}
?>
        </select>
        <input type=submit name=btn value='조회하기'>
        </form>
    </td></tr>
</table>
<table width=1000 align=center class='tb_layout bg0'>
    <tr>
        <td width=50% align=center id=bg1><font color=skyblue size=3>장 수 정 보</font></td>
        <td width=50% align=center id=bg1><font color=orange size=3>장 수 열 전</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?php generalInfo($gen); generalInfo2($gen); ?>
        </td>
        <td valign=top>
            <?=getGeneralHistoryAll($gen)?>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=orange size=3>전투 기록</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 결과</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=getBatLogRecent($gen, 24)?>
        </td>
        <td valign=top>
            <?=getBatResRecent($gen, 24)?>
        </td>
    </tr>
<?php if($npc > 1): ?>
    <tr>
        <td align=center id=bg1><font color=orange size=3>개인 기록</font></td>
        <td align=center id=bg1><font color=orange size=3>&nbsp;</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=getGenLogRecent($gen, 24)?>
        </td>
        <td valign=top>
        </td>
    </tr>
<?php endif; ?>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
