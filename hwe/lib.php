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


//디버그용 매크로
ini_set("session.cache_expire", 10080);      // minutes
include "MYDB.php";

// 각종 변수
define('STEP_LOG', true);
define('PROCESS_LOG', true);

ob_start();
session_cache_limiter('nocache');//NOTE: 캐시가 가능하도록 설정해야 할 수도 있음. 주의!
//FIXME: 이곳에서 설정하면 안될 듯 하다. 옮기자.

// 에러 메세지 출력
function Error($message='', $url="")
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

function LogText($prefix, $variable)
{
    $text = sprintf('%s : %s'."\n", $prefix, var_export($variable, true));
    file_put_contents(ROOT.'/d_log/dbg_logs.txt', $text, FILE_APPEND);
}

function extractMissingPostToGlobals()
{
    $result = [];
    if (isset($_POST) && count($_POST) > 0) {
        foreach($_POST as $key=>$val){
            if(is_numeric($key)){
                continue;
            }
            if(isset($GLOBALS[$key])){
                continue;
            }
            $result[$key]=$val;
            $GLOBALS[$key]=$val;
        }
    }

    if (isset($_GET) && count($_GET) > 0) {
        foreach($_GET as $key=>$val){
            if(is_numeric($key)){
                continue;
            }
            if(isset($GLOBALS[$key])){
                continue;
            }
            $result[$key]=$val;
            $GLOBALS[$key]=$val;
        }
    }

    if($result){
        LogText($_SERVER['REQUEST_URI'], $result);
    }
    
}
