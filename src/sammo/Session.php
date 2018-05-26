<?php
namespace sammo;

/**
 * Session Wrapper. 내부적으로 $_SESSION을 이용
 *
 * @property int    $userID    유저코드
 * @property string $userName  유저명
 * @property int    $userGrade 유저등급
 * @property string $ip        IP
 */
class Session
{
    const PROTECTED_NAMES = [
        'ip'=>true,
        'time'=>true,
        'userID'=>true,
        'userName'=>true,
        'userGrade'=>true,
        'writeClosed'=>true,
        'generalID'=>true,
        'generalName'=>true
    ];

    const GAME_KEY_DATE = '_g_loginDate';
    const GAME_KEY_GENERAL_ID = '_g_no';
    const GAME_KEY_GENERAL_NAME = '_g_name';
    const GAME_KEY_EXPECTED_DEADTIME = '_g_deadtime';
    

    private $writeClosed = false;
    private $sessionID = null;

    public static function getInstance(): Session
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Session();
        }
        return $inst;
    }

    public function restart(): Session
    {
        //NOTE: logout 프로세스는 아예 세션을 날려버리기도 하므로, 항상 안전하게 session_restart가 가능함을 보장하지 않음.
        ini_set('session.use_only_cookies', false);
        ini_set('session.use_cookies', false);
        ini_set('session.use_trans_sid', false);
        ini_set('session.cache_limiter', "none");
        session_id($this->sessionID);
        session_start(); // second session_start
        $this->writeClosed = false;
        return $this;
    }

    private static function die($result)
    {
        if (is_string($result)) {
            header('Location:'.$result);
            die();
        }

        $jsonResult = [
            'result'=>false,
            'reason'=>'로그인이 필요합니다.'
        ];

        if (!is_array($result)) {
            Json::die($jsonResult);
        }

        Json::die($result + $jsonResult);
    }

    public static function requireLogin($actionOnError = '..'): Session
    {
        $session = Session::getInstance();
        if ($session->isLoggedIn()) {
            return $session;
        }

        static::die($actionOnError);
    }

    public static function requireGameLogin($actionOnError = '..'): Session
    {
        $session = Session::requireLogin($actionOnError)->loginGame();

        if ($session->generalID) {
            return $session;
        }

        static::die($actionOnError);
    }

    public function __construct()
    {
        //session_cache_limiter('nocache, must_revalidate');

        // 세션 변수의 등록
        if (session_id() == "") {
            session_start();
            $this->sessionID = session_id();
        }

        //첫 등장

        if (!$this->get('ip')) {
            $this->set('ip', Util::get_client_ip(true));
            $this->set('time', time());
        }
    }

    public function setReadOnly(): Session
    {
        if (!$this->writeClosed) {
            session_write_close();
            $this->writeClosed = true;
        }
        return $this;
    }

    public function __set(string $name, $value)
    {
        if (key_exists($name, self::PROTECTED_NAMES)) {
            trigger_error("{$name}은 외부에서 쓰기 금지된 Session 변수입니다.", E_USER_NOTICE);
            return;
        }

        $this->set($name, $value);
    }

    private function set(string $name, $value)
    {
        if ($value === null) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION[$name] = $value;
        }
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

    public function login(int $userID, string $userName, int $grade): Session
    {
        $this->set('userID', $userID);
        $this->set('userName', $userName);
        $this->set('ip', Util::get_client_ip(true));
        $this->set('time', time());
        $this->set('userGrade', $grade);
        $this->set('access_token', null);
        return $this;
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
        $this->set('time', time());
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

        if (!class_exists('\\sammo\\DB') || !class_exists('\\sammo\\UniqueConst')) {
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

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $general = $db->queryFirstRow(
            'SELECT `no`, `name`, `killturn`, `turntime` from general where `owner` = %i',
            $userID
        );
        if (!$general) {
            if ($result !== null) {
                $result = false;
            }
            return $this;
        }
        
        $turnterm = $gameStor->turnterm;
        $isUnited = $gameStor->isUnited != 0;

        $generalID = $general['no'];
        $generalName = $general['name'];
        $nextTurn = new \DateTime($general['turntime']);
        $nextTurn = $nextTurn->getTimestamp();

        $deadTime = $nextTurn + $general['killturn'] * $turnterm;
        if ($deadTime < $now && !$isUnited) {
            $locked = $db->queryFirstField('SELECT plock FROM plock LIMIT 1');
            if (!$locked) {
                if ($result !== null) {
                    $result = false;
                }
                return $this;
            }
        }

        $db->update('general', [
            'logcnt' => $db->sqleval('logcnt+1'),
            'ip' => Util::get_client_ip(true),
            'lastConnect' => date('Y-m-d H:i:s')
        ], 'owner = %i', $userID);

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

    public function isLoggedIn()
    {
        if ($this->userID) {
            return true;
        } else {
            return false;
        }
    }

    public function __destruct()
    {
    }
}
