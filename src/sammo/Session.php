<?php
namespace sammo;

if(!defined('ROOT')){
    define('ROOT', '../..');
}

/**
 * Session Wrapper. 내부적으로 $_SESSION을 이용
 * 
 * @property int    $userID    유저코드
 * @property string $userName  유저명
 * @property int    $userGrade 유저등급
 * @property string $ip        IP
 */
class Session {

    const PROTECTED_NAMES = [
        'ip'=>true,
        'time'=>true, 
        'userID'=>true, 
        'userName'=>true, 
        'userGrade'=>true, 
        'writeClosed'=>true
    ];

    

    private $writeClosed = false;

    /**
     * @return \sammo\Session
     */
    public static function Instance(){
        static $inst = null;
        if($inst === null){
            $inst = new Session();
        }
        return $inst;
    }

    /**
     * @return \sammo\Session
     */
    public static function requireLogin($movePath = ROOT){
        $session = Session::Instance();
        if($session->isLoggedIn()){
            return $session;
        }

        $session->setReadOnly();

        if(is_string($path)){
            header('Location:'.$path);
            die();
        }

        Json::die([
            'result'=>false,
            'reason'=>'로그인이 필요합니다.'
        ]);
    }

    public function __construct() {
        //session_cache_limiter('nocache, must_revalidate');

        // 세션 변수의 등록
        if (session_id() == ""){
            session_start();
        }

        //첫 등장

        if(!Util::array_get($_SESSION['ip'])) {
            $_SESSION['ip'] = Util::get_client_ip(true);
            $_SESSION['time'] = time();
        }
    }

    /**
     * @return \sammo\Session
     */
    public function setReadOnly(){
        if(!$this->writeClosed){
            session_write_close();
            $this->writeClosed = true;
        }        
        return $this;
    }

    public function __set(string $name, $value){
        if(key_exists($key, $this->PROTECED_NAMES)){
            trigger_error("{$name}은 외부에서 쓰기 금지된 Session 변수입니다.", E_USER_NOTICE);
            return;
        }

        $this->set($name, $value);        
    }

    private function set(string $name, $value){
        if($value === null){
            unset($_SESSION[$name]);
        }
        else{
            $_SESSION[$name] = $value;
        }
    }

    public function __get(string $name){
        return $this->get($name);
    }

    private function get(string $name){
        return Util::array_get($_SESSION[$name]);
    }

    public function login(int $userID, string $userName, int $grade) {
        $this->set('userID', $userID);
        $this->set('userName', $userName);
        $this->set('ip', Util::get_client_ip(true));
        $this->set('time', time());
        $this->set('userGrade', $grade);
        $this->set('access_token', null);
        return $this;
    }

    public function logout() {
        $this->set('userID', null);
        $this->set('userName', null);
        $this->set('userGrade', null);
        $this->set('time', time());
        return $this;
    }

    /**
     * 로그인 유저의 전역 grade를 받아옴
     * @return int|null
     */
    public static function getUserGrade(bool $requireLogin = false, string $exitPath = ROOT){
        if($requireLogin){
            $obj = self::requireLogin($exitPath);
        }
        else{
            $obj = self::Instance();
        }
        
        return $obj->userGrade;
    }

    /** 
     * 로그인한 유저의 전역 id(숫자)를 받아옴 
     *
     * @return int|null 
     */
    public static function getUserID(bool $requireLogin = false, string $exitPath = ROOT){
        if($requireLogin){
            $obj = self::requireLogin($exitPath);
        }
        else{
            $obj = self::Instance();
        }
        
        return $obj->userID;
    }

    public function isLoggedIn() {
        if($this->userID){
            return true;
        }
        else{
            return false;
        }
    }

    public function __destruct() {
    }
}


