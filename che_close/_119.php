<?
include "lib.php";
include "func.php";

$connect=dbConn();

$query = "select userlevel from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me[userlevel] < 5) {
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

$query = "select turntime,tnmt_time from game";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select plock from plock";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$plock = MYDB_fetch_array($result);
?>

<html>
<head>
<title>삼국지 모의전투 PHP (유기체서버)</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
</head>
<body oncontextmenu='return false'>
    <form action=_119_b.php method=post>
    시간조정 : <input type=text size=3 name=minute><input type=submit name=btn value='분당김'><input type=submit name=btn value='분지연'> 최종갱신 : <?=$admin[turntime];?><br>
    시간조정 : <input type=text size=3 name=minute2><input type=submit name=btn value='토너분당김'><input type=submit name=btn value='토너분지연'> 토너먼트 : <?=$admin[tnmt_time];?><br>
    봉급지급 : <input type=submit name=btn value='금지급'><input type=submit name=btn value='쌀지급'><br>
    락 풀 기 : <input type=submit name=btn value='락걸기'><input type=submit name=btn value='락풀기'> 현재 : <?=$plock[plock]>0?"동결중":"가동중";?><br>
    </form>
</body>
</html>

