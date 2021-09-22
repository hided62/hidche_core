<?php

namespace sammo;

//https://hub.packtpub.com/eloquent-without-laravel/
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class RootDB
{
    private static $uDB = null;
    private static ?Capsule $uIlluminate = null;

    private static $host = '_tK_host_';
    private static $user = '_tK_user_';
    private static $password = '_tK_password_';
    private static $dbName = '_tK_dbName_';
    private static $port = _tK_port_;
    private static $encoding = 'utf8mb4';
    private static $collation = 'utf8mb4_general_ci';


    private static $globalSalt = '_tK_globalSalt_';

    private function __construct()
    {
    }

    public static function illuminate(): Capsule
    {
        if(self::$uIlluminate !== null){
            return self::$uIlluminate;
        }
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'   => 'mysql',
            'host'     => self::$host,
            'database' => self::$dbName,
            'username' => self::$user,
            'password' => self::$password,
            'charset'   => self::$encoding,
            'collation' => self::$collation,
        ], 'root');

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->bootEloquent();
        self::$uIlluminate = $capsule;
        return $capsule;
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

            self::$uDB->error_handler = function () {
            };
            self::$uDB->throw_exception_on_error = true;
            self::$uDB->throw_exception_on_nonsql_error = true;
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
}
