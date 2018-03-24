<?php
namespace sammo;

include "lib.php";
include "func.php";
include "schema.php";

if(Session::getUserGrade(true) < 5){
    die('관리자 아님');
}


// 파일로 DB 정보 저장
$file=@fopen("d_setting/conf.php","w") or Error("conf.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요","");
//@fwrite($file,"<?php /*\n$hostname\n$user_id\n$password\n$dbname\n */\n") or Error("conf.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요","");
@fclose($file);
@mkdir("data",0707);
@chmod("data",0707);
//@chmod("d_setting/conf.php",0707);

$temp=MYDB_fetch_array(MYDB_query("select count(*) from general where level = '1'", $connect));

MYDB_close($connect);

//echo "<script>location.replace('install2.php');</script>";
echo 'install2.php';//TODO:debug all and replace

// 관리자 테이블 생성
$db->query($game_schema);
// 장수 테이블 생성
$db->query($general_schema);
// 국가 테이블 생성
$db->query($nation_schema);
// 도시 테이블 생성
$db->query($city_schema);
// 부대 테이블 생성
$db->query($troop_schema);
// 토큰 테이블 생성
$db->query($token_schema);
// 외교 테이블 생성
$db->query($diplomacy_schema);
// 전당 테이블 생성
$db->query($hall_schema);
// 토너먼트 테이블 생성
$db->query($tournament_schema);
// 거래 테이블 생성
$db->query($auction_schema);
// 통계 테이블 생성
$db->query($statistic_schema);
// 연감 테이블 생성
$db->query($history_schema);