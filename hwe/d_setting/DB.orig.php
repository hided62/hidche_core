<?php

namespace sammo;

//https://hub.packtpub.com/eloquent-without-laravel/
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class DB
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

    private static $prefix = '_tK_prefix_';

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
        ]);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
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
        }
        return self::$uDB;
    }

    public static function prefix()
    {
        return self::$prefix;
    }
}
