<?php
namespace sammo;

define('ROOT', realpath(__dir__.'/..'));

mb_internal_encoding("UTF-8");
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8'); 


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
        'errno'=>$errno,
        'errstr'=>$errstr,
        'aux'=>'error',
        'trace'=>$e->getTraceAsString()
    ], Json::PRETTY);

    file_put_contents(ROOT.'/d_log/err_log.txt',"$data\n", FILE_APPEND);
}
set_error_handler("\sammo\logErrorByCustomHandler");


function logExceptionByCustomHandler(\Throwable $e){

    $date = date("Ymd_His");

    $data = Json::encode([
        'date'=>$date,
        'errno'=>$e->getCode(),
        'errstr'=>$e->getMessage(),
        'aux'=>'exception',
        'trace'=>$e->getTraceAsString()
    ], Json::PRETTY);

    file_put_contents(ROOT.'/d_log/err_log.txt',"$data\n", FILE_APPEND);
}
set_exception_handler('\\sammo\\logExceptionByCustomHandler');