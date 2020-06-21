<?php
namespace sammo;

class AppConf
{
    private static $serverList = null;

    /** @var string 전용 아이콘 경로 */
    public static $userIconPath = 'd_pic';

    /**
     * 서버 설정 반환
     *
     * @deprecated
     * @return \sammo\Setting[]
     */
    public static function getList()
    {
        return ServConfig::getServerList();
    }

    /**
     * DB 객체 생성
     *
     * @return \MeekroDB
     */
    public static function requireRootDB()
    {
        if (!class_exists('\\sammo\\RootDB')) {
            if(!trigger_error('RootDB.php가 설정되지 않았습니다.', E_USER_ERROR)){
                die();
            }
            
        }
        return RootDB::db();
    }

    /**
     * DB 객체 생성
     *
     * @return \MeekroDB
     */
    public static function requireDB()
    {
        if (!class_exists('\\sammo\\DB')) {
            if(!trigger_error('DB.php가 설정되지 않았습니다.', E_USER_ERROR)){
                die();
            }
            
        }
        return DB::db();
    }

    public static function getUserIconPathFS(string $filepath='') : string{
        $path = ROOT.'/'.static::$userIconPath; 
        if($filepath){
            $path .= '/'.$filepath;
        }
        return $path;
    }

    public static function getUserIconPathWeb(string $filepath='') : string{
        $path = ServConfig::$serverWebPath.'/'.static::$userIconPath;
        if($filepath){
            $path .= '/'.$filepath;
        }
        return $path;
    }
}
