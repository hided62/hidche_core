<?php
namespace sammo;

class DB{
    private static $uDB = null;

    private static $host = '_tK_host_';
    private static $user = '_tK_user_';
    private static $password = '_tK_password_';
    private static $dbName = '_tK_dbName_';
    private static $port = _tK_port_;
    private static $encoding = 'utf8mb4';

    private static $prefix = '_tK_prefix_';

    private function __construct(){

    }

    /**
     * DB 객체 생성
     * 
     * @return \MeekroDB 
     * @suppress PhanTypeMismatchProperty
     */
    public static function db(){
        if(self::$uDB === null){
            self::$uDB = new \MeekroDB(self::$host,self::$user,self::$password,self::$dbName,self::$port,self::$encoding);
            self::$uDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;

            self::$uDB->error_handler= function(){};
            self::$uDB->throw_exception_on_error = true;
            self::$uDB->throw_exception_on_nonsql_error = true;
        }
        return self::$uDB;
    }

    public static function prefix()
    {
        return self::$prefix;
    }
}