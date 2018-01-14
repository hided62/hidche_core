<?php
include "lib.php";
include "func.php";

$id  = $_POST[id];
$pw  = $_POST[pw];
$btn = $_POST[btn];

$connect = dbConn("sammo");

//회원 테이블에서 정보확인
$query = "select no,name,picture,grade from MEMBER where id='$id' and pw='$pw'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$member = MYDB_fetch_array($result);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

if($btn == "장수생성") {
    $site = "join.php";
} elseif($btn == "장수선택") {
    $site = "select_npc.php";
}
?>
<html>
<form name=form1 action=<?=$site;?> method=post>
    <input type=hidden name=id value='<?=$id;?>'>
    <input type=hidden name=pw value='<?=$pw;?>'>
</form>
a
<script>form1.submit();</script>
</html>
