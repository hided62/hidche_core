<?php
namespace sammo;

/** @var  \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('sammo\\', __DIR__.'/sammo', true);

$loader->addClassMap((function () {
    $d_settingMap = [];
    foreach (glob(__DIR__.'/d_setting/*.orig.php') as $filepath) {
        $filepath = str_replace('.orig.php', '.php', $filepath);
        if(!file_exists($filepath)) {
            continue;
        }
        $filename = basename($filepath);
        $classname = explode('.', $filename)[0];
        $d_settingMap['sammo\\'.$classname] = $filepath;
    };
    return $d_settingMap;
})());


//디버그용 매크로
ini_set("session.cache_expire", "10080");      // minutes

ob_start();

// 에러 메세지 출력
function Error($message='', $url="")
{
    if (!$url) {
        $url = $_SERVER['REQUEST_URI'];
    }
    $e = new \Exception();
    logError("aux_err", $message, '', getExceptionTraceAsString($e));

    $templates = new \League\Plates\Engine(__DIR__.'/templates');

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
    $text = sprintf('%s : %s'."\r\n", $prefix, TVarDumper::dump($variable));
    file_put_contents(ROOT.'/d_log/'.UniqueConst::$serverName.'_dbg_logs.txt', $text, FILE_APPEND);
}

function prepareDir(string $dirPath, bool $forceCreate=true):bool{
    if(file_exists($dirPath)){
        if(is_dir($dirPath)){
            return true;
        }
        if(!$forceCreate){
            throw new \RuntimeException('이미 파일이 있습니다');
        }
        if(!unlink($dirPath)){
            return false;
        }
        return mkdir($dirPath, 0775);
    }

    return mkdir($dirPath, 0775, true);
}