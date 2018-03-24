<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');


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

    public function set($key, $val) {
        $_SESSION[$key] = $val;
    }

    public function get($key) {
        return util::array_get($_SESSION[$key]);
    }

    public function login($noMember, $idMember, $grade) {
        $_SESSION['noMember'] = $noMember;
        $_SESSION['p_id'] = $idMember;
        $_SESSION['ip'] = util::get_client_ip(true);
        $_SESSION['time'] = time();
        $_SESSION['userGrade'] = $grade;
    }

    public function logout() {
        unset($_SESSION['noMember']);
        unset($_SESSION['p_id']);
        unset($_SESSION['userGrade']);
    }

    public function getGrade() {
        return $_SESSION['userGrade'];
    }

    public function isLoggedIn() {
        if($this->NoMember()){
            return true;
        }
        else{
            return false;
        }
    }

    public function NoMember() {
        return $this->get('noMember');
    }

    public function __destruct() {
    }
}


