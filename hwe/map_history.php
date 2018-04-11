<?php
namespace sammo;

include "lib.php";
include "func.php";

$year = Util::getReq('year', 'int');
$month = Util::getReq('month', 'int');

$url = '/a_history.php';

if(!strpos($_SERVER['HTTP_REFERER'], $url)) {
	Json::die([
		'result'=>false,
		'reason'=>'Invalid Referer'
	]);
}

if(!$year || !$month) {
	Json::die([
		'result'=>false,
		'reason'=>'year, month가 지정되지 않았습니다'
	]);
}


//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();
$connect=$db->get();

$map = $db->queryFirstField('SELECT map FROM history WHERE year=%i AND month=%i', $year, $month);

if(!$map){
	Json::die([
		'result'=>false,
		'reason'=>'해당하는 연월의 지도가 없습니다'
	]);
}

Json::die($map, Json::PASS_THROUGH);