<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin()->setReadOnly();
if($session->userGrade < 4) {
?>
<!DOCTYPE html>
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
관리자가 아닙니다.<br>
    <?=banner()?>
</body>
</html>
<?php
    exit();
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

list($turntime, $tnmt_time) = $gameStor->getValuesAsArray(['turntime','tnmt_time']);

$query = "select plock from plock";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$plock = MYDB_fetch_array($result);
?>
<!DOCTYPE html>
<html>
<head>
<title>삼국지 모의전투 HiDCHe</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
    <form action=_119_b.php method=post>
    시간조정 : <input type=text size=3 name=minute><input type=submit name=btn value='분당김'><input type=submit name=btn value='분지연'> 최종갱신 : <?=$turntime?><br>
    시간조정 : <input type=text size=3 name=minute2><input type=submit name=btn value='토너분당김'><input type=submit name=btn value='토너분지연'> 토너먼트 : <?=$tnmt_time?><br>
    봉급지급 : <input type=submit name=btn value='금지급'><input type=submit name=btn value='쌀지급'><br>
    락 풀 기 : <input type=submit name=btn value='락걸기'><input type=submit name=btn value='락풀기'> 현재 : <?=$plock['plock']>0?"동결중":"가동중"?><br>
    </form>
</body>
</html>

