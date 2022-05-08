<?php

namespace sammo;

define('ROOT', realpath(__DIR__ . '/..'));
setlocale(LC_ALL, 'ko_KR.UTF-8');
date_default_timezone_set('Asia/Seoul');
mb_internal_encoding("UTF-8");
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

ini_set("session.cache_expire", '10080');      // minutes
ini_set("session.gc_maxlifetime", '604800');    // seconds

function getFriendlyErrorType($type)
{
    switch ($type) {
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

function getExceptionTraceAsString($exception)
{
    //https://gist.github.com/abtris/1437966
    $rtn = "";
    $count = 0;
    $rtn = [];
    foreach ($exception->getTrace() as $frame) {
        $args = "";
        if (isset($frame['args'])) {
            $args = array();
            foreach ($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                } elseif (is_array($arg)) {
                    $args[] = "Array";
                } elseif (is_null($arg)) {
                    $args[] = 'NULL';
                } elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        $rtn[] = sprintf(
            "#%s %s:%s %s(%s)",
            $count,
            isset($frame['file']) ? $frame['file'] : 'unknown file',
            isset($frame['line']) ? $frame['line'] : 'unknown line',
            (isset($frame['class']))  ? $frame['class'] . $frame['type'] . $frame['function'] : $frame['function'],
            $args
        );
        $count++;
    }
    return $rtn;
}

function logError(string $err, string $errstr, string $errpath, array $trace)
{
    $fdb = FileDB::db(ROOT . '/d_log/err_log.sqlite3', ROOT . '/f_install/sql/err_log.sql');
    $date = date("Ymd_His");

    $errpath = str_replace(ROOT, '{ROOT}', $errpath);
    $trace = array_map(function (string $text) {
        return str_replace(ROOT, '{ROOT}', $text);
    }, $trace);

    $owner = Util::get_client_ip();
    $session = Session::getInstance();
    if ($session->isLoggedIn(true)) {
        $owner .= '(' . $session->getUserID() . ',' . $session->userName . ')';
    }

    $fdb->insert('err_log', [
        'date' => $date,
        'err' => $err,
        'errstr' => $errstr,
        'errpath' => $errpath,
        'trace' => Json::encode($trace),
        'webuser' => $owner
    ]);
}

function logErrorByCustomHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext=null)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    $e = new \Exception();

    logError(
        getFriendlyErrorType($errno),
        $errstr,
        $errfile . ':' . $errline,
        getExceptionTraceAsString($e)
    );
}
set_error_handler("\\sammo\\logErrorByCustomHandler");


function logExceptionByCustomHandler(\Throwable $e)
{

    logError(
        get_class($e),
        $e->getMessage(),
        $e->getFile() . ':' . $e->getLine(),
        getExceptionTraceAsString($e)
    );

    echo $e->getTraceAsString();
    throw $e;
}
set_exception_handler('\\sammo\\logExceptionByCustomHandler');

function getAPIExecutorClass($path){

    static $basePath = __NAMESPACE__.'\\API\\';
    if(is_string($path)){
    }
    else if(is_array($path)){
        $path = join('\\', $path);
    }
    else{
        throw new \InvalidArgumentException("{$path}는 올바른 API 지시자가 아님");
    }

    $classPath = str_replace('/', '\\', $basePath.$path);

    if(class_exists($classPath)){
        return $classPath;
    }
    throw new \InvalidArgumentException("{$path}는 올바른 API 경로가 아님");
}

function buildAPIExecutorClass($type, string $rootPath, array $args):\sammo\BaseAPI{
    $class = getAPIExecutorClass($type);
    return new $class($rootPath, $args);
}