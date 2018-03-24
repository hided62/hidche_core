<?php
include "lib.php";
include "func.php";

if(Session::getUserGrade(true) < 5){
    die('관리자 아님');
}



$db = getDB();

// 관리자 테이블 삭제
$db->query("DROP TABLE IF EXISTS game");
// 장수 테이블 삭제
$db->query("DROP TABLE IF EXISTS general");
// 국가 테이블 삭제
$db->query("DROP TABLE IF EXISTS nation");
// 도시 테이블 삭제
$db->query("DROP TABLE IF EXISTS city");
// 부대 테이블 삭제
$db->query("DROP TABLE IF EXISTS troop");
// 외교 테이블 삭제
$db->query("DROP TABLE IF EXISTS diplomacy");
// 토너먼트 테이블 삭제
$db->query("DROP TABLE IF EXISTS tournament");
// 거래 테이블 삭제
$db->query("DROP TABLE IF EXISTS auction");
// 통계 테이블 삭제
$db->query("DROP TABLE IF EXISTS statistic");
// 연감 테이블 삭제
$db->query("DROP TABLE IF EXISTS history");

// 삭제
unlink(__DIR__."/d_setting/conf.php");

delInDir("logs");
delInDir("data/session");
@unlink("data/connected.php");

