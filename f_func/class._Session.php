<?php

class _Session {
    public function __construct() {
        $sessionPath = ROOT.W.D_SESSION;
        session_save_path($sessionPath);
        session_cache_limiter('nocache, must_revalidate');
        session_cache_expire(10080);   // 60*24*7분
        session_set_cookie_params(604800, '/');

        // 세션 변수의 등록
        session_start();

        //첫 등장

        if(!isset($_SESSION['ip']) || $_SESSION['ip'] == '') {
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['time'] = time();
        }
    }

    public static function TrashSession() {
        $sessionPath = ROOT.W.D_SESSION;
        if($dir = @opendir($sessionPath)) {
            while($file = @readdir($dir)) {
                if(!strstr($file, 'sess_')) continue;
                if(strpos($file, 'sess_') != 0) continue;
                if(!$atime = @fileatime("{$sessionPath}/{$file}")) continue;
                if(time() > $atime+604800) {  // 3600*24*7초
                    @unlink("{$sessionPath}/{$file}");
                }
            }
            closedir($dir);
        }
    }

    public function Set($key, $val) {
        $_SESSION[$key] = $val;
    }

    public function Get($key) {
        return $_SESSION[$key];
    }

    public function Login($noMember) {
        $_SESSION['noMember'] = $noMember;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['time'] = time();
    }

    public function Logout() {
        $_SESSION['noMember'] = 0;
        session_destroy();
    }

    public function IsLoggedIn() {
        if(!isset($_SESSION['noMember'])){
            return false;
        }
        if($_SESSION['noMember'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function NoMember() {
        return $_SESSION['noMember'];
    }

    public function __destruct() {
    }
}


