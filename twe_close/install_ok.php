<?php
include "lib.php";
include "schema.php";
include "func.php";
require_once "../e_lib/util.php";
if(file_exists("d_setting/set.php")) error("이미 set.php가 생성되어 있습니다.<br><br>재설치하려면 해당 파일을 지우세요");


$hostname = util::array_get($_POST['hostname'], '');
$user_id = util::array_get($_POST['user_id'], '');
$password = util::array_get($_POST['password'], '');
$dbname = util::array_get($_POST['dbname'], '');

// 호스트네임, 아이디, DB네임, 비밀번호의 공백여부 검사
if(isBlank($hostname)) Error("HostName을 입력하세요","");
if(isBlank($user_id)) Error("User ID 를 입력하세요","");
if(isBlank($dbname)) Error("DB NAME을 입력하세요","");

// DB에 커넥트 하고 DB NAME으로 select DB
$connect = MYDB_connect($hostname,$user_id,$password) or Error("MySQL-DB Connect<br>Error!!!","");
if(MYDB_error($connect)) Error(__LINE__.MYDB_error($connect),"");
MYDB_select_db($dbname, $connect) or Error("MySQL-DB Select<br>Error!!!","");

delInDir("logs");
delInDir("data/session");
@unlink("data/connected.php");

// 관리자 테이블 삭제
if(isTable($connect, "game", $dbname)) @MYDB_query("drop table game", $connect) or Error("drop ".MYDB_error($connect),"");
//  락 테이블 삭제
if(isTable($connect, "plock", $dbname)) @MYDB_query("drop table plock", $connect) or Error("drop ".MYDB_error($connect),"");
// 장수 테이블 삭제
if(isTable($connect, "general", $dbname)) @MYDB_query("drop table general", $connect) or Error("drop ".MYDB_error($connect),"");
// 국가 테이블 삭제
if(isTable($connect, "nation", $dbname)) @MYDB_query("drop table nation", $connect) or Error("drop ".MYDB_error($connect),"");
// 도시 테이블 삭제
if(isTable($connect, "city", $dbname)) @MYDB_query("drop table city", $connect) or Error("drop ".MYDB_error($connect),"");
// 부대 테이블 삭제
if(isTable($connect, "troop", $dbname)) @MYDB_query("drop table troop", $connect) or Error("drop ".MYDB_error($connect),"");
// 토큰 테이블 삭제
if(isTable($connect, "token",$dbname)) @MYDB_query("drop table token", $connect) or Error(__LINE__.MYDB_error($connect),"");
// 외교 테이블 삭제
if(isTable($connect, "diplomacy", $dbname)) @MYDB_query("drop table diplomacy", $connect) or Error("drop ".MYDB_error($connect),"");
// 토너먼트 테이블 삭제
if(isTable($connect, "tournament", $dbname)) @MYDB_query("drop table tournament", $connect) or Error("drop ".MYDB_error($connect),"");
// 거래 테이블 삭제
if(isTable($connect, "auction", $dbname)) @MYDB_query("drop table auction", $connect) or Error("drop ".MYDB_error($connect),"");
// 통계 테이블 삭제
if(isTable($connect, "statistic",$dbname)) @MYDB_query("drop table statistic", $connect) or Error("drop ".MYDB_error($connect),"");
// 연감 테이블 삭제
if(isTable($connect, "history",$dbname)) @MYDB_query("drop table history", $connect) or Error("drop ".MYDB_error($connect),"");

// 관리자 테이블 생성
if(!isTable($connect, "game", $dbname)) @MYDB_query($game_schema, $connect) or Error("create game ".MYDB_error($connect),"");
// 락 테이블 생성
if(!isTable($connect, "plock", $dbname)) @MYDB_query($plock_schema, $connect) or Error("create plock ".MYDB_error($connect),"");
// 장수 테이블 생성
if(!isTable($connect, "general", $dbname)) @MYDB_query($general_schema, $connect) or Error("create general ".MYDB_error($connect),"");
// 국가 테이블 생성
if(!isTable($connect, "nation", $dbname)) @MYDB_query($nation_schema, $connect) or Error("create nation ".MYDB_error($connect),"");
// 도시 테이블 생성
if(!isTable($connect, "city", $dbname)) @MYDB_query($city_schema, $connect) or Error("create city ".MYDB_error($connect),"");
// 부대 테이블 생성
if(!isTable($connect, "troop", $dbname)) @MYDB_query($troop_schema, $connect) or Error("create troop ".MYDB_error($connect),"");
// 토큰 테이블 생성
if(!isTable($connect, "token",$dbname)) @MYDB_query($token_schema, $connect) or Error(__LINE__.MYDB_error($connect),"");
// 외교 테이블 생성
if(!isTable($connect, "diplomacy", $dbname)) @MYDB_query($diplomacy_schema, $connect) or Error("create diplomacy ".MYDB_error($connect),"");
// 전당 테이블 생성
if(!isTable($connect, "hall", $dbname)) {
    MYDB_query($hall_schema, $connect) or Error("create hall ".MYDB_error($connect),"");
    for($i=0; $i < 21; $i++) {
        for($j=0; $j < 10; $j++) {
            MYDB_query("insert into hall (type, rank) values ({$i}, {$j})", $connect);
        }
    }
}

// 왕조 테이블 생성
if(!isTable($connect, "emperior",$dbname)) @MYDB_query($emperior_schema, $connect) or Error("create emperior ".MYDB_error($connect),"");
// 토너먼트 테이블 생성
if(!isTable($connect, "tournament",$dbname)) @MYDB_query($tournament_schema, $connect) or Error("create tournament ".MYDB_error($connect),"");
// 거래 테이블 생성
if(!isTable($connect, "auction",$dbname)) @MYDB_query($auction_schema, $connect) or Error("create auction ".MYDB_error($connect),"");
// 통계 테이블 생성
if(!isTable($connect, "statistic",$dbname)) @MYDB_query($statistic_schema, $connect) or Error("create statistic ".MYDB_error($connect),"");
// 연감 테이블 생성
if(!isTable($connect, "history",$dbname)) @MYDB_query($history_schema, $connect) or Error("create history ".MYDB_error($connect),"");

// 파일로 DB 정보 저장
$file=@fopen("d_setting/set.php","w") or Error("set.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요","");
@fwrite($file,"<?php /*\n$hostname\n$user_id\n$password\n$dbname\n */\n") or Error("set.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요","");
@fclose($file);
@mkdir("data",0707);
@chmod("data",0707);
@chmod("d_setting/set.php",0707);

$temp=MYDB_fetch_array(MYDB_query("select count(*) from general where level = '1'", $connect));

MYDB_close($connect);

echo "<script>location.replace('install2.php');</script>";
