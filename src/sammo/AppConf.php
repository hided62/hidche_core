<?php
namespace sammo;

if(!defined('ROOT')){
    define('ROOT', '../..');
}

class AppConf{
    private static $serverList = null;

    public static function getList(){
        if(self::$serverList === null){
            self::$serverList = [
                'che'=>['체', 'white', new Setting(ROOT.'/che')],
                'kwe'=>['퀘', 'yellow', new Setting(ROOT.'/kwe')],
                'pwe'=>['풰', 'orange', new Setting(ROOT.'/pwe')],
                'twe'=>['퉤', 'magenta', new Setting(ROOT.'/twe')],
                'hwe'=>['훼', 'red', new Setting(ROOT.'/hwe')]
            ];
        }
        return self::$serverList;
    }

    /**
     * DB 객체 생성
     * 
     * @return \MeekroDB 
     */
    public static function requireRootDB(){
        if(!class_exists('\\sammo\\RootDB')){
            trigger_error('RootDB.php가 설정되지 않았습니다.', E_USER_ERROR);
            die();
        }
        return RootDB::db();
    }

    /**
     * DB 객체 생성
     * 
     * @return \MeekroDB 
     */
    public static function requireDB(){
        if(!class_exists('\\sammo\\DB')){
            trigger_error('DB.php가 설정되지 않았습니다.', E_USER_ERROR);
            die();
        }
        return DB::db();
    }
}