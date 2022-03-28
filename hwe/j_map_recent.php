<?php
namespace sammo;
use Nette\Caching\Cache;

include "lib.php";
include "func.php";

if(!class_exists('\\sammo\\UniqueConst')){
    Json::die([
        'result'=>false,
        'reason'=>'서버 초기화되지 않음'
    ]);
}

if(!prepareDir('data/file_cache')){
	Json::die([
		'result'=>false,
		'reason'=>'cache 불가'
	]);
}

$storage = new \Nette\Caching\Storages\FileStorage('data/file_cache');
$cache = new Cache($storage);
$serverID = UniqueConst::$serverID;

$mapInfo = $cache->load("recent_map");
//로그인 검사

$now = time();

if($mapInfo && ($now - $mapInfo['timestamp'] < 600)){
	$mapEtag = $mapInfo['etag'];
	$mapModified = $mapInfo['timestamp'];

    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mapModified)." GMT");
    header("Etag: $mapEtag");

    if (
		strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']??'2000-01-01') === $mapModified ||
		trim($_SERVER['HTTP_IF_NONE_MATCH']??'') === $mapEtag
	) {
        header("HTTP/1.1 304 Not Modified");
		die();
	}

	Json::die($mapInfo['data'], 0);
}

$defaultPost = [
    'year' => null,
    'month' => null,
    'aux' => null,
    'neutralView' => true,
    'showMe' => false,
];

$history = formatHistoryToHTML(getGlobalHistoryLogRecent(10));
$rawMap = getWorldMap([
    'year' => null,
    'month' => null,
    'aux' => null,
    'neutralView' => true,
    'showMe' => false,
]);

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$rawMap['history'] = $history;
$rawMap['theme'] = GameConst::$mapName;

$etag = hash('sha256', $serverID.$now);
$map = [
	'etag'=>$etag,
	'timestamp'=>$now,
	'data'=>$rawMap,
];
$cache->save("recent_map", $map);
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $now)." GMT");
header("Etag: $etag");

Json::die($map['data'], 0);