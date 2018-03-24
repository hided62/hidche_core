<?php
namespace sammo;

include "lib.php";
include "func.php";
include "schema.php";

if(Session::getUserGrade(true) < 5){
    echo '관리자 아님';
    die();
}

$connect = dbConn();




// 삭제
unlink("d_setting/conf.php");

// DB에 커넥트 하고 DB NAME으로 select DB
$connect = @MYDB_connect($hostname,$user_id,$password) or Error("MySQL-DB Connect<br>Error!!!","");
if(MYDB_error($connect)) Error(__LINE__.MYDB_error($connect),"");
MYDB_select_db($dbname, $connect ) or Error("MySQL-DB Select<br>Error!!!","");

delInDir("logs");
delInDir("data/session");
@unlink("data/connected.php");


// 관리자 테이블 삭제
if(isTable($connect, "game",$dbname)) @MYDB_query("DROP TABLE IF EXISTS game", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 장수 테이블 삭제
if(isTable($connect, "gen",$dbname)) @MYDB_query("DROP TABLE IF EXISTS general", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 국가 테이블 삭제
if(isTable($connect, "nation",$dbname)) @MYDB_query("DROP TABLE IF EXISTS nation", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 도시 테이블 삭제
if(isTable($connect, "city",$dbname)) @MYDB_query("DROP TABLE IF EXISTS city", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 부대 테이블 삭제
if(isTable($connect, "troop",$dbname)) @MYDB_query("DROP TABLE IF EXISTS troop", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 토큰 테이블 삭제
if(isTable($connect, "token",$dbname)) @MYDB_query("DROP TABLE IF EXISTS token", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 외교 테이블 삭제
if(isTable($connect, "diplomacy",$dbname)) @MYDB_query("DROP TABLE IF EXISTS diplomacy", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 토너먼트 테이블 삭제
if(isTable($connect, "tournament",$dbname)) @MYDB_query("DROP TABLE IF EXISTS tournament", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 거래 테이블 삭제
if(isTable($connect, "auction",$dbname)) @MYDB_query("DROP TABLE IF EXISTS auction", $connect) or Error("drop ".MYDB_error($connect),"");
// 통계 테이블 삭제
if(isTable($connect, "statistic",$dbname)) @MYDB_query("DROP TABLE IF EXISTS statistic", $connect) or Error("drop ".MYDB_error($connect),"");
// 연감 테이블 삭제
if(isTable($connect, "history",$dbname)) @MYDB_query("DROP TABLE IF EXISTS history", $connect) or Error("drop ".MYDB_error($connect),"");

// 관리자 테이블 생성
if(!isTable($connect, "game",$dbname)) @MYDB_query($game_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 장수 테이블 생성
if(!isTable($connect, "gen",$dbname)) @MYDB_query($general_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 국가 테이블 생성
if(!isTable($connect, "nation",$dbname)) @MYDB_query($nation_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 도시 테이블 생성
if(!isTable($connect, "city",$dbname)) @MYDB_query($city_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 부대 테이블 생성
if(!isTable($connect, "troop",$dbname)) @MYDB_query($troop_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 토큰 테이블 생성
if(!isTable($connect, "token",$dbname)) @MYDB_query($token_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 외교 테이블 생성
if(!isTable($connect, "diplomacy",$dbname)) @MYDB_query($diplomacy_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 전당 테이블 생성
if(!isTable($connect, "hall",$dbname)) @MYDB_query($hall_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 토너먼트 테이블 생성
if(!isTable($connect, "tournament",$dbname)) @MYDB_query($tournament_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 거래 테이블 생성
if(!isTable($connect, "auction",$dbname)) @MYDB_query($auction_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 통계 테이블 생성
if(!isTable($connect, "statistic",$dbname)) @MYDB_query($statistic_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 연감 테이블 생성
if(!isTable($connect, "history",$dbname)) @MYDB_query($history_schema, $connect) or Error("create history ".MYDB_error($connect),"");

// 파일로 DB 정보 저장
$file=@fopen("d_setting/conf.php","w") or Error("conf.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요","");
@fwrite($file,"<?php /*\n$hostname\n$user_id\n$password\n$dbname\n */\n") or Error("conf.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요","");
@fclose($file);
@mkdir("data",0707);
@chmod("data",0707);
@chmod("d_setting/conf.php",0707);

$temp=MYDB_fetch_array(MYDB_query("select count(*) from general where level = '1'", $connect));

MYDB_close($connect);

//echo "<script>location.replace('install2.php');</script>";
echo 'install2.php';//TODO:debug all and replace