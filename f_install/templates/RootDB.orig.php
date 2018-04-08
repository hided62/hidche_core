<?php
namespace sammo;

class RootDB
{
    private static $uDB = null;

    private static $host = '_tK_host_';
    private static $user = '_tK_user_';
    private static $password = '_tK_password_';
    private static $dbName = '_tK_dbName_';
    private static $port = _tK_port_;
    private static $encoding = 'utf8mb4';

    private static $globalSalt = '_tK_globalSalt_';

    private function __construct()
    {
    }

    /**
     * DB 객체 생성
     *
     * @return \MeekroDB
     * @suppress PhanTypeMismatchProperty
     */
    public static function db()
    {
        if (self::$uDB === null) {
            self::$uDB = new \MeekroDB(self::$host, self::$user, self::$password, self::$dbName, self::$port, self::$encoding);
            self::$uDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;
        }
        return self::$uDB;
    }

    /**
     * 비밀번호 해시용 전역 SALT 반환
     * 비밀번호는 sha512(usersalt|sha512(globalsalt|password|globalsalt)|usersalt); 순임
     *
     * @return string
     */
    public static function getGlobalSalt()
    {
        return self::$globalSalt;
    }

    /**
     * 서버 주소 반환. 서버의 경로가 하부 디렉토리인 경우에 하부 디렉토리까지 포함
     *
     * @return string
     */
    public static function getServerBasepath()
    {
        //FIXME: 더 좋은 위치가 있을 것.
        return self::$globalSalt;
    }
}
