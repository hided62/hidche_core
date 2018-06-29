<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 0);
if($type < 0 || $type > 4){
    $type = 0;
}
extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if($session->userGrade < 5) {
?>
<!DOCTYPE html>
<html>
<head>
<title>관리메뉴</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
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
$connect=$db->get();

$sel[$type] = "selected";

?>
<!DOCTYPE html>
<html>
<head>
<title>접속정보</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>접 속 정 보<br><?=closeButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0]??''?> value=0>접속률</option>
            <option <?=$sel[1]??''?> value=1>총갱신</option>
            <option <?=$sel[2]??''?> value=2>갱신/턴</option>
            <option <?=$sel[3]??''?> value=3>총로그인</option>
            <option <?=$sel[4]??''?> value=4>갱신/로그인</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<table align=center class='tb_layout bg0'>
    <tr id=bg1>
        <td align=center width=120>장수명</td>
        <td align=center width=50>접속률</td>
        <td align=center width=40>시작연령</td>
        <td align=center width=40>연령</td>
        <td align=center width=80>총갱신</td>
        <td align=center width=80>갱신/턴</td>
        <td align=center width=80>총로그인</td>
        <td align=center width=100>갱신/로그인</td>
    </tr>
<?php
switch($type) {
    case 0: $query = "select name,connect,startage,age,refcnt,logcnt,refcnt/(age-startage+1)/12 as ref,refcnt/logcnt as log from general order by connect desc limit 0,30"; break;
    case 1: $query = "select name,connect,startage,age,refcnt,logcnt,refcnt/(age-startage+1)/12 as ref,refcnt/logcnt as log from general order by refcnt desc limit 0,30"; break;
    case 2: $query = "select name,connect,startage,age,refcnt,logcnt,refcnt/(age-startage+1)/12 as ref,refcnt/logcnt as log from general order by ref desc limit 0,30"; break;
    case 3: $query = "select name,connect,startage,age,refcnt,logcnt,refcnt/(age-startage+1)/12 as ref,refcnt/logcnt as log from general order by logcnt desc limit 0,30"; break;
    case 4: $query = "select name,connect,startage,age,refcnt,logcnt,refcnt/(age-startage+1)/12 as ref,refcnt/logcnt as log from general order by log desc limit 0,30"; break;
}
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

for($i=0; $i < $gencount; $i++) {
    $gen = MYDB_fetch_array($genresult);
    echo "
    <tr>
        <td align=center>{$gen['name']}</td>
        <td align=center>{$gen['connect']}</td>
        <td align=center>{$gen['startage']}</td>
        <td align=center>{$gen['age']}</td>
        <td align=center>".round($gen['refcnt']/2, 1)."</td>
        <td align=center>".round($gen['ref']/2,1)."</td>
        <td align=center>{$gen['logcnt']}</td>
        <td align=center>".round($gen['log']/2,1)."</td>
    </tr>";
}

?>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
