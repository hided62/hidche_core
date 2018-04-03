<?php
namespace sammo;

define('IMAGE', '../../image');
define('IMAGES', '../../images');
//define('IMAGE', 'http://115.68.28.99/image');
//define('IMAGES', 'http://115.68.28.99/images');

define('ROOT', realpath(__dir__.'/..'));

function CustomHeader() {
    //xxx: CustomHeader를 제거하기 전까진 유지
    WebUtil::setHeaderNoCache();
}

function logErrorByCustomHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext){
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    $date = date("Ymd_His");

    file_put_contents(__DIR__.'/../d_log/err_log.txt',"$date, $errno, $errstr, $errfile, $errline\n", FILE_APPEND);
    
    /* Don't execute PHP internal error handler */
    //return true;
}
set_error_handler("\sammo\logErrorByCustomHandler");

function Error($msg) {
    file_put_contents(ROOT.'/d_log/err.txt', $msg."\n", FILE_APPEND);
    exit(1);
}
