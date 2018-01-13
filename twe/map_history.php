<?php
$year = $_GET['year'];
$month = $_GET['month'];

$url = '/a_history.php';

if(!strpos($_SERVER['HTTP_REFERER'], $url)) {
	exit(1);
}

if(!$year || !$month) {
	exit(1);
}

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select map from history where year='$year' and month='$month'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$history = MYDB_fetch_array($result);

if(!$history['map']) {
	exit(1);
}

$map = $history['map'];
$map = str_replace('<_quot_>', "'", $map);
$map = str_replace('<_dquot_>', '"', $map);

echo $map;
