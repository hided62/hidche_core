<?php
namespace sammo;

/**
 * Dummy Session
 *
 * @property int    $userID    유저코드
 * @property string $userName  유저명
 * @property int    $userGrade 유저등급
 * @property string $ip        IP
 * @property bool   $reqOTP    인증 코드 필요
 * @property array  $acl       권한
 * @property string $tokenValidUntil 로그인 토큰 길이
 * 
 * @property int    $generalID   장수 번호 (게임 로그인 필요)
 * @property string $generalName 장수 이름 (게임 로그인 필요)
 */
class DummySession extends Session
{
    private $writeClosed = false;
    private $sessionInfo = [];

    public static function getInstance(): Session
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new DummySession();
        }
        return $inst;
    }

    public function restart(): Session
    {
        $this->sessionInfo = [];
        return $this;
    }


    public function __construct()
    {
        $this->set('userID', -1);
        $this->set('userName', 'Dummy');
        $this->set('ip', '127.0.0.1');
        $this->set('time', time());
        $this->set('userGrade', '-1');
        $this->set('acl', '[]');
        $this->set('reqOTP', false);
        $this->set('tokenValidUntil', null);

    }

    public function setReadOnly(): Session
    {
        $this->writeClosed = true;
        return $this;
    }

    private function set(string $name, $value)
    {
        $this->sessionInfo[$name] = $value;
    }

    public function __get(string $name)
    {
        if ($name == 'generalID') {
            if (!class_exists('\\sammo\\UniqueConst')){
                return null;
            }
            return $this->get(UniqueConst::$serverID.static::GAME_KEY_GENERAL_ID);
        }
        if ($name == 'generalName') {
            if (!class_exists('\\sammo\\UniqueConst')){
                return null;
            }
            return $this->get(UniqueConst::$serverID.static::GAME_KEY_GENERAL_NAME);
        }
        return $this->get($name);
    }

    private function get(string $name)
    {
        return Util::array_get($_SESSION[$name]);
    }

    public function login(int $userID, string $userName, int $grade, bool $reqOTP, ?string $tokenValidUntil, array $acl): Session
    {
        $this->set('userID', $userID);
        $this->set('userName', $userName);
        $this->set('ip', Util::get_client_ip(true));
        $this->set('time', time());
        $this->set('userGrade', $grade);
        $this->set('acl', $acl);
        $this->set('reqOTP', $reqOTP);
        $this->set('tokenValidUntil', $tokenValidUntil);
        return $this;
    }

    public function setReqOTP(bool $reqOTP=false, string $tokenValidUntil){
        $this->set('reqOTP', $reqOTP);
        $this->set('tokenValidUntil', $tokenValidUntil);
    }


    public function logout(): Session
    {
        if ($this->writeClosed) {
            $this->restart();
        }
        if (class_exists('\\sammo\\UniqueConst')) {
            $this->logoutGame();
        }
        
        $this->set('userID', null);
        $this->set('userName', null);
        $this->set('userGrade', null);
        $this->set('acl', null);
        $this->set('reqOTP', null);
        $this->set('time', time());
        $this->set('lastMsgGet', null);
        return $this;
    }

    public function loginGame(&$result = null): Session
    {
        $userID = $this->userID;
        if (!$userID) {
            if ($result !== null) {
                $result = false;
            }
            return $this;
        }

        if (!class_exists('\\sammo\\UniqueConst')) {
            if ($result !== null) {
                $result = false;
            }
            return $this;
        }

        $serverID = UniqueConst::$serverID;

        $loginDate = $this->get($serverID.static::GAME_KEY_DATE);
        $generalID = $this->get($serverID.static::GAME_KEY_GENERAL_ID);
        $generalName = $this->get($serverID.static::GAME_KEY_GENERAL_NAME);
        $deadTime = $this->get($serverID.static::GAME_KEY_EXPECTED_DEADTIME);

        $now = time();
        if (
            $generalID && $generalName && $loginDate && $deadTime
            && $loginDate + 1800 > $now && $deadTime > $now
        ) {
            //로그인 정보는 30분간 유지한다.
            if ($result !== null) {
                $result = true;
            }
            return $this;
        }

        if ($generalID || $generalName || $loginDate || $deadTime) {
            $this->logoutGame();
        }

        $generalID = -1;
        $generalName = 'DummyGeneral';
        $deadTime = $now+60*60*24;

        $this->set($serverID.static::GAME_KEY_DATE, $now);
        $this->set($serverID.static::GAME_KEY_GENERAL_ID, $generalID);
        $this->set($serverID.static::GAME_KEY_GENERAL_NAME, $generalName);
        $this->set($serverID.static::GAME_KEY_EXPECTED_DEADTIME, $deadTime);
        return $this;
    }

    public function logoutGame(): Session
    {
        if ($this->writeClosed) {
            $this->restart();
        }
        $serverID = UniqueConst::$serverID;
        $this->set($serverID.static::GAME_KEY_DATE, null);
        $this->set($serverID.static::GAME_KEY_GENERAL_ID, null);
        $this->set($serverID.static::GAME_KEY_GENERAL_NAME, null);
        $this->set($serverID.static::GAME_KEY_EXPECTED_DEADTIME, null);
        return $this;
    }


    /**
     * 로그인 유저의 전역 grade를 받아옴
     * @return int|null
     */
    public static function getUserGrade(bool $requireLogin = false, string $exitPath = '..')
    {
        if ($requireLogin) {
            $obj = self::requireLogin($exitPath);
        } else {
            $obj = self::getInstance();
        }
        
        return $obj->userGrade;
    }

    /**
     * 로그인한 유저의 전역 id(숫자)를 받아옴
     *
     * @return int|null
     */
    public static function getUserID(bool $requireLogin = false, string $exitPath = '..')
    {
        if ($requireLogin) {
            $obj = self::requireLogin($exitPath);
        } else {
            $obj = self::getInstance();
        }
        
        return $obj->userID;
    }

    /**
     * 
     */
    public static function getGeneralID(bool $requireLogin = false, string $exitPath = '..')
    {
        if ($requireLogin) {
            $obj = self::requireLogin($exitPath);
        } else {
            $obj = self::getInstance();
        }
        
        return $obj->generalID??0;
    }

    public function isLoggedIn(bool $ignoreOTP = false): bool
    {
        if(!$ignoreOTP){
            if($this->reqOTP){
                return false;
            }
            if(!$this->tokenValidUntil){
                return false;
            }
            $now = TimeUtil::now();
            if($this->tokenValidUntil < $now){
                return false;
            }
        }
        
        if ($this->userID) {
            return true;
        } else {
            return false;
        }
    }

    public function isGameLoggedIn(): bool
    {
        if ($this->generalID) {
            return true;
        } else {
            return false;
        }
    }

    public function __destruct()
    {
    }
}
