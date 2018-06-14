<?php
namespace sammo;

define('ROOT', realpath(__dir__.'/..'));
setlocale(LC_ALL, 'ko_KR.UTF-8');
date_default_timezone_set('Asia/Seoul');
mb_internal_encoding("UTF-8");
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8'); 

ini_set("session.cache_expire", 10080);      // minutes
ini_set("session.gc_maxlifetime", 604800);    // seconds

function getFriendlyErrorType($type) 
{ 
    switch($type) 
    { 
        case E_ERROR: // 1 // 
            return 'E_ERROR'; 
        case E_WARNING: // 2 // 
            return 'E_WARNING'; 
        case E_PARSE: // 4 // 
            return 'E_PARSE'; 
        case E_NOTICE: // 8 // 
            return 'E_NOTICE'; 
        case E_CORE_ERROR: // 16 // 
            return 'E_CORE_ERROR'; 
        case E_CORE_WARNING: // 32 // 
            return 'E_CORE_WARNING'; 
        case E_COMPILE_ERROR: // 64 // 
            return 'E_COMPILE_ERROR'; 
        case E_COMPILE_WARNING: // 128 // 
            return 'E_COMPILE_WARNING'; 
        case E_USER_ERROR: // 256 // 
            return 'E_USER_ERROR'; 
        case E_USER_WARNING: // 512 // 
            return 'E_USER_WARNING'; 
        case E_USER_NOTICE: // 1024 // 
            return 'E_USER_NOTICE'; 
        case E_STRICT: // 2048 // 
            return 'E_STRICT'; 
        case E_RECOVERABLE_ERROR: // 4096 // 
            return 'E_RECOVERABLE_ERROR'; 
        case E_DEPRECATED: // 8192 // 
            return 'E_DEPRECATED'; 
        case E_USER_DEPRECATED: // 16384 // 
            return 'E_USER_DEPRECATED'; 
    } 
    return "{$type}"; 
}

function logErrorByCustomHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext){
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    $date = date("Ymd_His");
    $e = new \Exception();

    $data = Json::encode([
        'date'=>$date,
        'err'=>getFriendlyErrorType($errno),
        'errstr'=>$errstr,
        'trace'=>explode("\n", $e->getTraceAsString())
    ], Json::PRETTY);

    file_put_contents(ROOT.'/d_log/err_log.txt',"$data\n", FILE_APPEND);
}
set_error_handler("\sammo\logErrorByCustomHandler");


function logExceptionByCustomHandler(\Throwable $e){

    $date = date("Ymd_His");

    $data = Json::encode([
        'date'=>$date,
        'err'=>get_class($e),
        'errstr'=>$e->getMessage(),
        'trace'=>explode("\n", $e->getTraceAsString())
    ], Json::PRETTY);

    file_put_contents(ROOT.'/d_log/err_log.txt',"$data\n", FILE_APPEND);
    echo $e->getTraceAsString();
    throw $e;
}
set_exception_handler('\\sammo\\logExceptionByCustomHandler');