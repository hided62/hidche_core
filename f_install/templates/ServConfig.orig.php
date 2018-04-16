<?php
namespace sammo;

class ServConfig
{
    private function __construct()
    {
    }

    public static $serverWebPath = '_tK_serverBasePath_';
    public static $sharedIconPath = '_tK_sharedIconPath_';
    public static $gameImagePath = "_tK_gameImagePath_";

    public static function getSharedIconPath(string $filepath='') : string
    {
        if($filepath){
            return static::$sharedIconPath."/{$filepath}";
        }
        return static::$sharedIconPath;
    }
    
    public static function getUserIconPath(string $filepath='') : string
    {
        return AppConf::getUserIconPathWeb($filepath);
    }

    public static function getGameImagePath(string $filepath='') : string
    {
        if($filepath){
            return static::$gameImagePath."/{$filepath}";
        }
        return static::$gameImagePath;
    }


    /**
     * 서버 주소 반환. 서버의 경로가 하부 디렉토리인 경우에 하부 디렉토리까지 포함
     *
     * @return string
     */
    public static function getServerBasepath() : string
    {
        return self::$serverWebPath;
    }
}