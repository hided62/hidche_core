<?php
require(__dir__.'/../vendor/autoload.php');
use utilphp\util as util;

class _Session {
    public function __construct() {
        //session_cache_limiter('nocache, must_revalidate');

        // 세션 변수의 등록
        session_start();

        //첫 등장

        if(!util::array_get($_SESSION['ip'])) {
            $_SESSION['ip'] = util::get_client_ip(true);
            $_SESSION['time'] = time();
        }
    }

    public function Set($key, $val) {
        $_SESSION[$key] = $val;
    }

    public function Get($key) {
        return $_SESSION[$key];
    }

    public function Login($noMember, $idMember, $grade) {
        $_SESSION['noMember'] = $noMember;
        $_SESSION['p_id'] = $idMember;
        $_SESSION['ip'] = util::get_client_ip(true);
        $_SESSION['time'] = time();
        $_SESSION['userGrade'] = $grade;
    }

    public function Logout() {
        unset($_SESSION['noMember']);
        unset($_SESSION['p_id']);
        unset($_SESSION['time']);
        unset($_SESSION['userGrade']);
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


