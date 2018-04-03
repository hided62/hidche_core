<?php
namespace sammo;

/** @var  \Composer\Autoload\ClassLoader $loader */
$loader = require __dir__.'/../vendor/autoload.php';
$loader->addPsr4('sammo\\', __DIR__.'/sammo', true);

$loader->addClassMap((function(){
    $d_settingMap = [];
    foreach(glob(__dir__.'/d_setting/*.php') as $filepath){
        $filename = basename($filepath);
        if(Util::ends_with($filename, '.orig.php')){
            continue;
        }
        $classname = explode('.', $filename)[0];
        $d_settingMap['sammo\\'.$classname] = $filepath;
    };
    return $d_settingMap;
})());


/******************************************************************************
체섭용 인클루드 파일
 ******************************************************************************/

// W3C P3P 규약설정
//    @header ("P3P : CP=\"ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC\"");

//디버그용 매크로
ini_set("session.cache_expire", 10080);      // minutes


include "MYDB.php";

// 각종 변수
define('STEP_LOG', true);
define('PROCESS_LOG', true);
$_startTime = microtime(true);
$_ver     = "서비스중";
$x_version = "삼국지 모의전투 PHP HideD v0.1";
$x_banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : 유기체(jwh1807@gmail.com), HideD(hided62@gmail.com)";
$x_helper = "도움 주신 분들";
$x_develrate = 50;   // 내정시 최하 민심 설정
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
$x_goodgenleader = 65;  // 명장,지장에 사용될 통솔 제한
$x_goodgenpower = 65;  // 명장에 사용될 무력 제한
$x_goodgenintel = 65;  // 지장에 사용될 지력 제한
$_basecolor = "000044"; // 기본 배경색깔 푸른색
$_basecolor2 = "225500"; // 기본 배경색깔 초록색
$_basecolor3 = "660000"; // 기본 배경색깔 붉은색
$_basecolor4 = "330000"; // 기본 배경색깔 검붉은색
$_taxrate = 0.01;   // 군량 매매시 세율
//$images = "http://115.68.28.99/images";
//$image = "http://115.68.28.99/image";
//$images = "http://jwh1807.vipweb.kr/images";
//$image = "http://jwh1807.vipweb.kr/image";
$image1 = "../d_pic";
$images = "/images";
$image = "/image";

session_cache_limiter('nocache');//NOTE: 캐시가 가능하도록 설정해야 할 수도 있음. 주의!
//FIXME: 이곳에서 설정하면 안될 듯 하다. 옮기자.

ob_start();

// MySQL 데이타 베이스에 접근
function dbConn($isRoot=false)
{
    if ($isRoot) {
        return RootDB::db()->get();
    }
    return DB::db()->get();
}

// 에러 메세지 출력
function Error($message, $url="")
{
    if(!$url){
        $url = $_SERVER['REQUEST_URI'];
    }
    WebUtil::setHeaderNoCache();
    file_put_contents(__dir__."/logs/_db_bug.txt", "{\"url\":\"$url\",\"msg\":\"$message\"}\n", FILE_APPEND);

    $templates = new \League\Plates\Engine('templates');

    ob_get_flush();

    die($templates->render('error', [
        'message' => $msg
    ]));
}

// 게시판의 생성유무 검사
function isTable($connect, $str, $dbname='')
{
    if (!$dbname) {
        $f=@file("d_setting/DB.php") or Error("DB.php파일이 없습니다. DB설정을 먼저 하십시요");
        for ($i=1;$i<=4;$i++) {
            $f[$i]=str_replace("\n", "", $f[$i]);
        }
        $dbname=$f[4];
    }

    $result = MYDB_list_tables($dbname, $connect) or Error(__LINE__." : list_table error : ".MYDB_error($connect), "");

    $cnt = MYDB_num_rows($result);
    for ($i=0; $i < $cnt; $i++) {
        $tablename = MYDB_fetch_row($result);
        if ($str == $tablename[0]) {
            return 1;
        }
    }

    return 0;
}

function MessageBox($str)
{
    echo "<script>alert('$str');</script>";
}

function PrintElapsedTime()
{
    global $_startTime;
    $_endTime = round(microtime(true) - $_startTime, 3);
    echo "<table width=1000 align=center style=font-size:10;><tr><td align=right>경과시간 : {$_endTime}초</td></tr></table>";
}

function LogText($prefix, $variable)
{
    $fp = fopen('logs/dbg_logs.txt', 'a+');
    if ($fp == false) {
        $directory_name = dirname('logs/dbg_logs.txt');
        if (!is_dir($directory_name)) {
            mkdir($directory_name);
            $fp = fopen('logs/dbg_logs.txt', 'a+');
        }
    }
    fwrite($fp, sprintf('%s : %s'."\n", $prefix, var_export($_POST, true)));
    fclose($fp);
}


if (isset($_POST) && count($_POST) > 0) {
    LogText($_SERVER['REQUEST_URI'], $_POST);
}
extract($_POST, EXTR_SKIP);
//XXX: $_POST를 추출 없이 그냥 쓰는 경우가 많아서 일단 디버깅을 위해 씀!!!! 절대 production 서버에서 사용 금지!
//todo: $_POST로 제공되는 데이터를 각 페이지마다 분석할것.
