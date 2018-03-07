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
        header('Content-Type: text/html; charset=utf-8');
    }
//define(CURPATH, 'f_async');
//define(FILE, substr(strrchr(__FILE__, "\\"), 1));
}

function getmicrotime() {
    $microtimestmp = explode(' ', microtime());
    return $microtimestmp[0] + $microtimestmp[1];
}

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


