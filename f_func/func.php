<?php
require(__dir__.'/../vendor/autoload.php');

function SetHeaderNoCache(){
    if(!headers_sent()) {
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    }
}

function CustomHeader() {
    //FIXME: 왜 Contect-Type이 text/html로 고정이지?!
    if(!headers_sent()) {
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
//        header('Cache-Control: public');
//        header('Pragma: public');
//        header('Content-Type: text/html; charset=utf-8');
    }
//define(CURPATH, 'f_async');
//define(FILE, substr(strrchr(__FILE__, "\\"), 1));
}

function getmicrotime() {
    $microtimestmp = explode(' ', microtime());
    return $microtimestmp[0] + $microtimestmp[1];
}

function logErrorByCustomHandler(int $errno , string $errstr, string $errfile, int $errline, array $errcontext){
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    $date = date("Ymd_His");

    file_put_contents(__DIR__.'/../d_log/err_log.txt',"$date, $errno, $errstr, $errfile, $errline\n");
    
    /* Don't execute PHP internal error handler */
    return true;
}
set_error_handler("logErrorByCustomHandler");

function Error($msg) {
    AppendToFile(ROOT.'/d_log/err.txt', $msg."\n");
    exit(1);
}

function ErrorToScreen($msg) {
    AppendToFile(ROOT.'/d_log/err.txt', $msg."\n");
    echo $msg;
    exit(1);
}

function WriteToFile($filename, $content) {
    $fp = @fopen($filename, 'w');
    @fwrite($fp, $content);
    @fclose($fp);
}

function AppendToFile($filename, $content) {
    $fp = @fopen($filename, 'a');
    @fwrite($fp, $content);
    @fclose($fp);
}

function ReadToFile($filename) {
    $fp = @fopen($filename, 'r');
    $content = @fread($fp, filesize($filename));
    @fclose($fp);
    return $content;
}

function ReadToFileForward($filename, $size) {
    $fp = @fopen($filename, 'r');
    $content = @fread($fp, $size);
    @fclose($fp);
    return $content;
}

function ReadToFileBackward($filename, $size) {
    $fp = @fopen($filename, 'r');
    @fseek($fp, -$size, SEEK_END);
    $content = @fread($fp, $size);
    @fclose($fp);
    return $content;
}

function delInDir($dir) {
    $handle = opendir($dir);
    while(false !== ($FolderOrFile = readdir($handle))) {
        if($FolderOrFile != "." && $FolderOrFile != "..") {
            if(is_dir("$dir/$FolderOrFile")) {
                delInDir("$dir/$FolderOrFile");
            } // recursive
            else {
                unlink("$dir/$FolderOrFile");
            }
        }
    }
    closedir($handle);
    return $success;
}

function delExpiredInDir($dir, $t) {
    $handle = opendir($dir);
    while(false !== ($FolderOrFile = readdir($handle))) {
        if($FolderOrFile != "." && $FolderOrFile != "..") {
            if(is_dir("$dir/$FolderOrFile")) {
                delExpiredInDir("$dir/$FolderOrFile", $t);
            } // recursive
            else {
                $mt = filemtime("$dir/$FolderOrFile");
                if($mt < $t) {
                    unlink("$dir/$FolderOrFile");
                }
            }
        }
    }
    closedir($handle);
    return $success;
}


function returnJson($value, $noCache = true, $pretty = false, $die = true){
    header('Content-Type: application/json');

    if($noCache){
        header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
    }

    if($pretty){
        $flag = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
    }
    else{
        $flag = JSON_UNESCAPED_UNICODE;
    }
    echo json_encode($value, $flag); 
    if($die){
        die();
    }
}

function hashPassword($salt, $password){
    return hash('sha512', $salt.$password.$salt);
}

/**
 * 변환할 내용이 _tK_$key_ 형태로 작성된 단순한 템플릿 파일을 이용하여 결과물을 생성해주는 함수.
 */
function generateFileUsingSimpleTemplate(string $srcFilePath, string $destFilePath, array $params, bool $canOverwrite=false){
    if($destFilePath === $srcFilePath){
        return 'invalid destFilePath';
    }
    if(!file_exists($srcFilePath)){
        return 'srcFilePath is not exists';
    }
    if(file_exists($destFilePath) && !$canOverwrite){
        return 'destFilePath is already exists';
    }
    if(!is_writable($destFilePath)){
        return 'destFilePath is not writabble';
    }

    $text = file_get_contents($srcFilePath);
    foreach($params as $key => $value){
        $text = str_replace("_tK_{$key}_", $value);
    }
    file_put_contents($destFilePath, $text);

    return true;
}