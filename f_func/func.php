<?php
require(__dir__.'/../vendor/autoload.php');

function SetHeaderNoCache(){
    if(!headers_sent()) {
        header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
    }
}

function CustomHeader() {
    //xxx: CustomHeader를 제거하기 전까진 유지
    SetHeaderNoCache();
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
    if($noCache){
        SetHeaderNoCache();
    }
    
    header('Content-Type: application/json');

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
    if(!is_writable(dirname($destFilePath))){
        return 'destFilePath is not writable';
    }

    $text = file_get_contents($srcFilePath);
    foreach($params as $key => $value){
        $text = str_replace("_tK_{$key}_", $value, $text);
    }
    file_put_contents($destFilePath, $text);

    return true;
}

/**
 * '비교적' 안전한 int 변환
 * null -> null
 * int -> int
 * float -> int
 * numeric(int, float) 포함 -> int
 * 기타 -> 예외처리
 * 
 * @return int|null
 */
function toInt($val, $force=false){
    if($val === null){
        return null;
    }
    if(is_int($val)){
        return $val;
    }
    if(is_numeric($val)){
        return intval($val);//
    }
    if($val === 'NULL' || $val === 'null'){
        return null;
    }

    if($force){
        return intval($val);
    }
    throw new InvalidArgumentException('올바르지 않은 타입형 :'.$val);
}

/**
 * Generate a random string, using a cryptographically secure 
 * pseudorandom number generator (random_int)
 * 
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}
