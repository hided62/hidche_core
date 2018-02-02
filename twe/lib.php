<?php
require(__dir__.'/../vendor/autoload.php');

use utilphp\util as util;

/******************************************************************************
체섭용 인클루드 파일
 ******************************************************************************/

// W3C P3P 규약설정
//    @header ("P3P : CP=\"ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC\"");

//디버그용 매크로
define('__OLINE__',__LINE__);
define('__LINE__',__FILE__." ".__FUNCTION__." ".__LINE__." : ");
ini_set("session.cache_expire", 10080);      // minutes
ini_set("session.gc_maxlifetime", 604800);    // seconds

ob_start();

include "MYDB.php";
require_once(__dir__.'/d_setting/conf.php');

// 각종 변수
define('STEP_LOG', true);
define('PROCESS_LOG', true);
$_startTime = getMicroTime();
$_ver     = "서비스중";
$_version = "삼국지 모의전투 PHP HideD v0.1";
$_banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : 유기체(jwh1807@gmail.com), HideD(hided62@gmail.com)";
$_helper = "도움 주신 분들";
$_develrate = 50;   // 내정시 최하 민심 설정
$_upgradeLimit = 30;    // 능력치 상승 경험치
$_dexLimit = 1000000;    // 숙련도 제한치
$_defaultatmos = 40;  // 초기 사기치
$_defaulttrain = 40;  // 초기 훈련치
$_defaultatmos2 = 70;  // 초기 사기치
$_defaulttrain2 = 70;  // 초기 훈련치
$_maxtrain = 100;   // 최대 훈련치
$_maxatmos = 100;   // 인위적으로 올릴 수 있는 최대 사기치
$_maximumatmos = 150;   // 최대 사기치
$_maximumtrain = 110;   // 최대 훈련치
$_training = 30;  // 풀징병시 훈련 1회 상승량
$_atmosing = 0.98;  // 훈련시 사기 감소율
$_basefiring = 0.25; // 계략 기본 성공률
$_firing = 300;    // 계략시 확률 가중치(수치가 클수록 변화가 적음 : (지력차/$_firing + $_basefiring)
$_firingbase = 100; // 계략시 기본 수치 감소량
$_firingpower = 400; // 계략시 수치 감소량($_firingbase ~ $_firingpower)
$_goodgenleader = 65;  // 명장,지장에 사용될 통솔 제한
$_goodgenpower = 65;  // 명장에 사용될 무력 제한
$_goodgenintel = 65;  // 지장에 사용될 지력 제한
$_basecolor = "000044"; // 기본 배경색깔 푸른색
$_basecolor2 = "225500"; // 기본 배경색깔 초록색
$_basecolor3 = "660000"; // 기본 배경색깔 붉은색
$_basecolor4 = "330000"; // 기본 배경색깔 검붉은색
$_armperphase = 500;    // 페이즈당 표준 감소 병사 수
$_basegold = 0;  // 기본 국고
$_baserice = 2000;  // 기본 병량
$_taxrate = 0.01;   // 군량 매매시 세율
//$images = "http://115.68.28.99/images";
//$image = "http://115.68.28.99/image";
//$images = "http://jwh1807.vipweb.kr/images";
//$image = "http://jwh1807.vipweb.kr/image";
$image1 = "../d_pic";
    $images = "/images";
    $image = "/image";

unset($member);
unset($setup);

// Data, Icon, 세션디렉토리의 쓰기 권한이 없다면 에러 처리
// 단, 폴더가 없는 경우라면 폴더를 생성 할 필요가 있음. 
// data폴더가 없으면 data/session까지 생성
if(is_dir("data")){
	if(!is_writable("data")) Error("Data 디렉토리의 쓰기 권한이 없습니다!");
	if(is_dir("data/session")){
		if(!is_writable("data/session")) Error("세션 디렉토리 data/session의 쓰기 권한이 없습니다!");	
	}else{
		mkdir("data/session");
	}		
}else{
	mkdir("data");
	mkdir("data/session");
}



session_save_path('data/session');
session_cache_limiter('nocache, must_revalidate');//NOTE: 캐시가 가능할 수도 있음. 주의!
session_set_cookie_params(0, '/');
session_cache_expire(60);   // 60분

// 세션 변수의 등록
//NOTE: ajax등의 경우에는 session_write_close로 빠르게 끝낼 수 있어야한다.
session_start();

//첫 등장
if(!util::array_get($_SESSION['p_ip'], null)) {
    $_SESSION['p_ip'] = getenv("REMOTE_ADDR");
    $_SESSION['p_time'] = time();
}

//id, 이름, 국가는 로그인에서
//초과된 세션은 로그아웃(1시간)
if($_SESSION['p_time']+3600 < time()) {
    unset($_SESSION['p_id']);
    unset($_SESSION[getServPrefix().'p_name']);
    $_SESSION['p_time'] = time();
    session_destroy();
} else {
    $_SESSION['p_time'] = time();
}

// DB가 설정이 되었는지를 검사
if(!file_exists("d_setting/set.php")&&!preg_match("/install/i",$_SERVER['PHP_SELF'])) {
//    echo"<meta http-equiv=refresh content='0;url=../'>";
echo $_SERVER['PHP_SELF'].'//'.preg_match("/install/i",$_SERVER['PHP_SELF']);
    exit;
}

// MySQL 데이타 베이스에 접근
function dbconn($table = "") {
    //TODO:dbconn 사용하는 모든 녀석들을 없애야한다.
    global $connect, $HTTP_COOKIE_VARS;
    $f = @file("d_setting/set.php") or Error("set.php파일이 없습니다. DB설정을 먼저 하십시요!");
    for($i=1; $i<= 4; $i++) $f[$i] = trim(str_replace("\n","",$f[$i]));
    if(!$connect) $connect = @MYDB_connect($f[1],$f[2],$f[3]) or Error("DB 접속시 에러가 발생했습니다");
    if($table != "") { $f[4] = $table; }
    @MYDB_select_db($f[4], $connect) or Error("DB Select 에러가 발생했습니다","");
    return $connect;
}

// 에러 메세지 출력
function Error($message, $url="") {
    global $setup, $connect, $dir, $config_dir;
    include "error.php";
    if($connect) @MYDB_close($connect);
    exit;
}

function returnJson($value, $noCache = true, $pretty = false, $die = true){
    header('Content-Type: application/json');

    if($noCache){
        header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
    }

    if($pretty){
        $flag = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
    }
    else{
        $flag = JSON_UNESCAPED_UNICODE;
    }
    echo json_encode($value, $flag); 
    if($die){
        die();
    }
}

// 게시판의 생성유무 검사
function isTable($connect, $str, $dbname='') {
    if(!$dbname) {
        $f=@file("d_setting/set.php") or Error("set.php파일이 없습니다. DB설정을 먼저 하십시요");
        for($i=1;$i<=4;$i++) $f[$i]=str_replace("\n","",$f[$i]);
        $dbname=$f[4];
    }

    $result = MYDB_list_tables($dbname, $connect) or Error(__LINE__." : list_table error : ".MYDB_error($connect),"");

    $cnt = MYDB_num_rows($result);
    for($i=0; $i < $cnt; $i++) {
        $tablename = MYDB_fetch_row($result);
        if($str == $tablename[0]) return 1;
    }

    return 0;
}

// 빈문자열 경우 1을 리턴
function isblank($str) {
    //FIXME: 리턴 값은 boolean이 더 적절하다.
    $temp=str_replace("　","",$str);
    $temp=str_replace("\n","",$temp);
    $temp=strip_tags($temp);
    $temp=str_replace("&nbsp;","",$temp);
    $temp=str_replace(" ","",$temp);
    if(preg_match("/[^[:space:]]/i",$temp)) return 0;
    return 1;
}

function Debug($str) {
    echo "<script>alert('$str');</script>";
}

function MessageBox($str) {
    echo "<script>alert('$str');</script>";
}

function getmicrotime() {
    $microtimestmp = explode(' ', microtime());
    return $microtimestmp[0] + $microtimestmp[1];
}

function PrintElapsedTime() {
    global $_startTime;
    $_endTime = round(getMicroTime() - $_startTime, 3);
    echo "<table width=1000 align=center style=font-size:10;><tr><td align=right>경과시간 : {$_endTime}초</td></tr></table>";
}

/**
 * '비교적' 안전한 int 변환
 * null -> null
 * int -> int
 * float -> int
 * numeric(int, float) 포함 -> int
 * 기타 -> 예외처리
 * 
 * @return int|null
 */
function toInt($val){
    if($val === null){
        return null;
    }
    if(is_int($val)){
        return $val;
    }
    if(is_numeric($val)){
        return intval($val);//
    }

    throw new InvalidArgumentException('올바르지 않은 타입형 :'.$val);
}

function LogText($prefix, $variable){
    $fp = fopen('logs/dbg_logs.txt', 'a+');
    if($fp == false){
        $directory_name = dirname('logs/dbg_logs.txt');
        if(!is_dir($directory_name)){
            mkdir($directory_name);
            $fp = fopen('logs/dbg_logs.txt', 'a+');
        }
    }
    fwrite($fp, sprintf('%s : %s'."\n", $prefix, var_export($_POST, true)));
    fclose($fp);
}

function dictToArray($dict, $keys){
    $result = [];

    foreach($keys as $key){
        $result[] = util::array_get($dict[$key], null);
    }
    return $result;
}

function parseJsonPost(){
    // http://thisinterestsme.com/receiving-json-post-data-via-php/
    // http://thisinterestsme.com/php-json-error-handling/
    if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
        throw new Exception('Request method must be POST!');
    }
    
    //Make sure that the content type of the POST request has been set to application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if(strcasecmp($contentType, 'application/json') != 0){
        throw new Exception('Content type must be: application/json');
    }
    
    //Receive the RAW post data.
    $content = trim(file_get_contents("php://input"));
    
    //Attempt to decode the incoming RAW post data from JSON.
    $decoded = json_decode($content, true);
    
    
    $jsonError = json_last_error();
    
    //In some cases, this will happen.
    if(is_null($decoded) && $jsonError == JSON_ERROR_NONE){
        throw new Exception('Could not decode JSON!');
    }
    
    //If an error exists.
    if($jsonError != JSON_ERROR_NONE){
        $error = 'Could not decode JSON! ';
        
        //Use a switch statement to figure out the exact error.
        switch($jsonError){
            case JSON_ERROR_DEPTH:
                $error .= 'Maximum depth exceeded!';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $error .= 'Underflow or the modes mismatch!';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $error .= 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $error .= 'Malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                 $error .= 'Malformed UTF-8 characters found!';
            break;
            default:
                $error .= 'Unknown error!';
            break;
        }
        throw new Exception($error);
    }

    return $decoded;
}

if(isset($_POST) && count($_POST) > 0){
    LogText($_SERVER['REQUEST_URI'], $_POST);
}
extract($_POST, EXTR_SKIP); 
//XXX: $_POST를 추출 없이 그냥 쓰는 경우가 많아서 일단 디버깅을 위해 씀!!!! 절대 production 서버에서 사용 금지!
//todo: $_POST로 제공되는 데이터를 각 페이지마다 분석할것.