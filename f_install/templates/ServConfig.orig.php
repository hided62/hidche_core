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

    public static function getSharedIconPath() : string
    {
        static $path = '';
        if($path){
            $path;
        }

        if (Util::starts_with('http', self::$sharedIconPath)
            || Util::starts_with('//', self::$sharedIconPath))
        {
            $path = self::$sharedIconPath;
        }
        else{
            $path = self::$serverWebPath.'/'.self::$sharedIconPath;
        }
        return $path;
    }
    
    public static function getUserIconPath() : string
    {
        return AppConf::getUserIconPathWeb();
    }

    public static function getGameImagePath() : string
    {
        static $path = '';
        if($path){
            $path;
        }

        if (Util::starts_with('http', self::$gameImagePath)
            || Util::starts_with('//', self::$gameImagePath))
        {
            $path = self::$gameImagePath;
        }
        else{
            $path = self::$serverWebPath.'/'.self::$gameImagePath;
        }
        return $path;
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