<?
/******************************************************************************
체섭용 인클루드 파일
 ******************************************************************************/

// W3C P3P 규약설정
//    @header ("P3P : CP=\"ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC\"");

//디버그용 매크로
define(__OLINE__,__LINE__);
define(__LINE__,__FILE__." ".__FUNCTION__." ".__LINE__." : ");
ini_set("session.cache_expire", 10080);      // minutes
ini_set("session.gc_maxlifetime", 604800);    // seconds

ob_start();

include "MYDB.php";

// 각종 변수
define(STEP_LOG, true);
define(PROCESS_LOG, true);
$_startTime = getMicroTime();
$_ver     = "서비스중";
$_version = "삼국지 모의전투 PHP v2.29.1";
$_banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : 유기체(jwh1807@gmail.com)";
$_helper = "도움 주신 분들 : 하후연묘재, 자소유, 모모리, 반사대선, 마킹, 뒷집할머니, 허기, 헹이, 나나, 유키, SARS";
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
$images = "http://jwh1807.vipweb.kr/images";
$image = "http://jwh1807.vipweb.kr/image";
$image1 = "../d_pic";
//    $images = "images";
//    $image = "image";

unset($member);
unset($setup);

// Data, Icon, 세션디렉토리의 쓰기 권한이 없다면 에러 처리
if(!is_writable("data")) Error("Data 디렉토리의 쓰기 권한이 없습니다!");
if(!is_writable("data/session")) Error("세션 디렉토리 data/session의 쓰기 권한이 없습니다!");

session_save_path('data/session');
session_cache_limiter('nocache, must_revalidate');
session_set_cookie_params(0, '/');
session_cache_expire(60);   // 60분

// 세션 변수의 등록
session_start();

//첫 등장
if($_SESSION[p_ip] == "") {
    $_SESSION[p_ip] = getenv("REMOTE_ADDR");
    $_SESSION[p_time] = time();
}

//id, 이름, 국가는 로그인에서
//초과된 세션은 로그아웃(1시간)
if($_SESSION[p_time]+3600 < time()) {
    $_SESSION[p_id] = "";
    $_SESSION[p_name] = "";
    $_SESSION[p_nation] = 0;
    $_SESSION[p_time] = time();
    session_destroy();
} else {
    $_SESSION[p_time] = time();
}

// DB가 설정이 되었는지를 검사
if(!file_exists("d_setting/set.php")&&!preg_match("/install/i",$_SERVER['PHP_SELF'])) {
//    echo"<meta http-equiv=refresh content='0;url=../'>";
echo $_SERVER['PHP_SELF'].'//'.preg_match("/install/i",$_SERVER['PHP_SELF']);
    exit;
}

// MySQL 데이타 베이스에 접근
function dbconn($table = "") {
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
?>
