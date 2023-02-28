<?php
namespace sammo;

/**
 * Session Wrapper. 내부적으로 $_SESSION을 이용
 *
 * @property int    $userID    유저코드
 * @property string $userName  유저명
 * @property int    $userGrade 유저등급
 * @property string $ip        IP
 * @property bool   $reqOTP    인증 코드 필요
 * @property array  $acl       권한
 * @property int|null $tokenID  토큰ID
 * @property string $tokenValidUntil 로그인 토큰 길이
 *
 * @property int    $generalID   장수 번호 (게임 로그인 필요)
 * @property string $generalName 장수 이름 (게임 로그인 필요)
 */
class Session
{
    const PROTECTED_NAMES = [
        'ip'=>true,
        'reqOTP'=>true,
        'time'=>true,
        'userID'=>true,
        'userName'=>true,
        'userGrade'=>true,
        'writeClosed'=>true,
        'generalID'=>true,
        'generalName'=>true,
        'tokenValidUntil'=>true,
        'acl'=>true
    ];

    const GAME_KEY_DATE = '_g_loginDate';
    const GAME_KEY_GENERAL_ID = '_g_no';
    const GAME_KEY_GENERAL_NAME = '_g_name';
    const GAME_KEY_EXPECTED_DEADTIME = '_g_deadtime';


    protected $writeClosed = false;
    private $sessionID = null;

    protected static $instance = null;

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function restart(): static
    {
        //NOTE: logout 프로세스는 아예 세션을 날려버리기도 하므로, 항상 안전하게 session_restart가 가능함을 보장하지 않음.
        ini_set('session.use_only_cookies', 'false');
        ini_set('session.use_cookies', 'false');
        ini_set('session.use_trans_sid', 'false');
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

    public static function requireLogin($actionOnError = '..'): static
    {
        $session = static::getInstance();
        if ($session->isLoggedIn()) {
            return $session;
        }

        static::die($actionOnError);
    }

    public static function requireGameLogin($actionOnError = '..'): static
    {
        $session = static::requireLogin($actionOnError)->loginGame();

        if ($session->generalID) {
            return $session;
        }

        static::die($actionOnError);
    }

    protected function __construct()
    {
        //session_cache_limiter('nocache, must_revalidate');

        // 세션 변수의 등록
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->sessionID = session_id();
        }

        //첫 등장

        if (!$this->get('ip')) {
            $this->set('ip', Util::get_client_ip(true));
            $this->set('time', time());
        }
    }

    public function setReadOnly(): static
    {
        if (!$this->writeClosed) {
            session_write_close();
            $this->writeClosed = true;
        }
        return $this;
    }

    public function __set(string $name, $value)
    {
        if (key_exists($name, static::PROTECTED_NAMES)) {
            trigger_error("{$name}은 외부에서 쓰기 금지된 Session 변수입니다.", E_USER_NOTICE);
            return;
        }

        $this->set($name, $value);
    }

    protected function set(string $name, $value)
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

    protected function get(string $name)
    {
        return Util::array_get($_SESSION[$name]);
    }

    public function login(int $userID, string $userName, int $grade, bool $reqOTP, ?string $tokenValidUntil, ?int $tokenID, array $acl): static
    {
        $this->set('userID', $userID);
        $this->set('userName', $userName);
        $this->set('ip', Util::get_client_ip(true));
        $this->set('time', time());
        $this->set('userGrade', $grade);
        $this->set('acl', $acl);
        $this->set('reqOTP', $reqOTP);
        $this->set('tokenValidUntil', $tokenValidUntil);
        $this->set('tokenID', $tokenID);
        return $this;
    }

    public function setReqOTP(bool $reqOTP=false, string $tokenValidUntil){
        $this->set('reqOTP', $reqOTP);
        $this->set('tokenValidUntil', $tokenValidUntil);
    }


    public function logout(): static
    {
        if ($this->writeClosed) {
            $this->restart();
        }
        if (class_exists('\\sammo\\UniqueConst')) {
            $this->logoutGame();
        }

        if($this->tokenID??null){
            RootDB::db()->delete('login_token', 'id = %i', $this->tokenID);
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

    public function loginGame(&$result = null): static
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
        $isUnited = $gameStor->isunited != 0;

        $generalID = $general['no'];
        $generalName = $general['name'];
        $nextTurn = new \DateTime($general['turntime']);
        $nextTurn = $nextTurn->getTimestamp();

        $deadTime = $nextTurn + $general['killturn'] * $turnterm;
        if ($deadTime < $now && !$isUnited) {
            $locked = $db->queryFirstField('SELECT plock FROM plock WHERE `type` = "GAME" LIMIT 1');
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
            'lastConnect' => TimeUtil::now()
        ], 'owner = %i', $userID);

        $this->set($serverID.static::GAME_KEY_DATE, $now);
        $this->set($serverID.static::GAME_KEY_GENERAL_ID, $generalID);
        $this->set($serverID.static::GAME_KEY_GENERAL_NAME, $generalName);
        $this->set($serverID.static::GAME_KEY_EXPECTED_DEADTIME, $deadTime);
        return $this;
    }

    public function logoutGame(): static
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
            $obj = static::requireLogin($exitPath);
        } else {
            $obj = static::getInstance();
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
            $obj = static::requireLogin($exitPath);
        } else {
            $obj = static::getInstance();
        }

        return $obj->userID;
    }

    /**
     *
     */
    public static function getGeneralID(bool $requireLogin = false, string $exitPath = '..')
    {
        if ($requireLogin) {
            $obj = static::requireLogin($exitPath);
        } else {
            $obj = static::getInstance();
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
