<?php
namespace sammo;

if(!defined('ROOT')){
    define('ROOT', '../..');
}

class AppConf{
    private static $serverList = null;
    private static $rootSetting = null;

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

    public static function getRoot(){
        if(self::$rootSetting === null){
            self::$rootSetting = new Setting(ROOT);
        }
        return self::$rootSetting;
    }
}