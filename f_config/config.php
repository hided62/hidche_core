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

    file_put_contents(ROOT.'/d_log/err_log.txt',"$date, $errno, $errstr, $errfile, $errline\n", FILE_APPEND);
    
    /* Don't execute PHP internal error handler */
    //return true;
}
set_error_handler("\sammo\logErrorByCustomHandler");
