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
    protected $sessionInfo = [];

    public function restart(): static
    {
        $this->sessionInfo = [];
        return $this;
    }


    protected function __construct()
    {
        $this->set('userID', -1);
        $this->set('userName', 'Dummy');
        $this->set('ip', '127.0.0.1');
        $this->set('time', time());
        $this->set('userGrade', '-1');
        $this->set('acl', '[]');
        $this->set('reqOTP', false);
        $this->set('tokenValidUntil', '2999-12-31 23:59:59');

    }

    public function setReadOnly(): static
    {
        $this->writeClosed = true;
        return $this;
    }

    protected function set(string $name, $value)
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

    protected function get(string $name)
    {
        return $this->sessionInfo[$name] ?? null;
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

    public function __destruct()
    {
    }
}
