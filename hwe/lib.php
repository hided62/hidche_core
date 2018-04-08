<?php
namespace sammo;

/** @var  \Composer\Autoload\ClassLoader $loader */
$loader = require __dir__.'/../vendor/autoload.php';
$loader->addPsr4('sammo\\', __DIR__.'/sammo', true);

$loader->addClassMap((function () {
    $d_settingMap = [];
    foreach (glob(__dir__.'/d_setting/*.php') as $filepath) {
        $filename = basename($filepath);
        if (Util::ends_with($filename, '.orig.php')) {
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
$_taxrate = 0.01;   // 군량 매매시 세율

ob_start();
session_cache_limiter('nocache');//NOTE: 캐시가 가능하도록 설정해야 할 수도 있음. 주의!
//FIXME: 이곳에서 설정하면 안될 듯 하다. 옮기자.

// 에러 메세지 출력
function Error($message, $url="")
{
    if (!$url) {
        $url = $_SERVER['REQUEST_URI'];
    }
    file_put_contents(__dir__."/logs/_db_bug.txt", "{\"url\":\"$url\",\"msg\":\"$message\"}\n", FILE_APPEND);

    $templates = new \League\Plates\Engine('templates');

    ob_get_flush();
    WebUtil::setHeaderNoCache();

    die($templates->render('error', [
        'message' => $message
    ]));
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
    $text = sprintf('%s : %s'."\n", $prefix, var_export($variable, true));
    file_put_contents(ROOT.'/d_log/dbg_logs.txt', $text, FILE_APPEND);
}

function extractSuperGlobals()
{
    if (isset($_POST) && count($_POST) > 0) {
        LogText($_SERVER['REQUEST_URI'], $_POST);
        
        foreach($_POST as $key=>$val){
            if(isset($GLOBALS[$key])){
                continue;
            }
            $GLOBALS[$key]=$val;
        }
    }

    if (isset($_GET) && count($_GET) > 0) {
        LogText($_SERVER['REQUEST_URI'], $_GET);
        
        foreach($_GET as $key=>$val){
            if(isset($GLOBALS[$key])){
                continue;
            }
            $GLOBALS[$key]=$val;
        }
    }
}


extractSuperGlobals();
