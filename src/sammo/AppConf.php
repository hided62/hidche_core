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
     * @return \sammo\Setting[]
     */
    public static function getList()
    {
        if (self::$serverList === null) {
            self::$serverList = [
                'che'=>new Setting(ROOT.'/che', '체', 'white'),
                'kwe'=>new Setting(ROOT.'/kwe', '퀘', 'yellow'),
                'pwe'=>new Setting(ROOT.'/pwe', '풰', 'orange'),
                'twe'=>new Setting(ROOT.'/twe', '퉤', 'magenta'),
                'hwe'=>new Setting(ROOT.'/hwe', '훼', 'red')
            ];
        }
        return self::$serverList;
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
