<?php
namespace sammo;

include "lib.php";
include "func.php";

//TODO: 이게 뭔지 분석

$btn = $_POST['btn'];
$session = Session::requireLogin()->setReadOnly();
$connect = dbConn(true);

//회원 테이블에서 정보확인
$query = "select no,name,picture,grade from MEMBER where no='$userID'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$member = MYDB_fetch_array($result);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}



if($btn == "장수생성") {
    header('Location:join.php');
} elseif($btn == "장수선택") {
    header('Location:select_npc.php');
}