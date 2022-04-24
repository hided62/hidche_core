<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin()->setReadOnly();
if($session->userGrade < 4) {
    die(requireAdminPermissionHTML());
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

[$turntime, $tnmt_time] = $gameStor->getValuesAsArray(['turntime','tnmt_time']);

$plock = $db->queryFirstField('SELECT plock FROM plock WHERE `type` ="GAME"');
?>
<!DOCTYPE html>
<html>
<head>
<title>삼국지 모의전투 HiDCHe</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?= WebUtil::printDist('ts', 'common', true) ?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
</head>
<body>
    <form action=_119_b.php method=post>
    시간조정 : <input type=text size=3 name=minute><input type=submit name=btn value='분당김'><input type=submit name=btn value='분지연'> 최종갱신 : <?=$turntime?><br>
    시간조정 : <input type=text size=3 name=minute2><input type=submit name=btn value='토너분당김'><input type=submit name=btn value='토너분지연'> 토너먼트 : <?=$tnmt_time?><br>
    봉급지급 : <input type=submit name=btn value='금지급'><input type=submit name=btn value='쌀지급'><br>
    락 풀 기 : <input type=submit name=btn value='락걸기'><input type=submit name=btn value='락풀기'> 현재 : <?=$plock>0?"동결중":"가동중"?><br>
    </form>
</body>
</html>
