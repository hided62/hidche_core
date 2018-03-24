<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_func/class.Setting.php');


function getServerConfigList(){
    static $serverList = null;
    if($serverList !== null){
        return $serverList;
    }
    $serverList = [
        'che'=>['체', 'white', new Setting(__DIR__.'/../che')],
        'kwe'=>['퀘', 'yellow', new Setting(__DIR__.'/../kwe')],
        'pwe'=>['풰', 'orange', new Setting(__DIR__.'/../pwe')],
        'twe'=>['퉤', 'magenta', new Setting(__DIR__.'/../twe')],
        'hwe'=>['훼', 'red', new Setting(__DIR__.'/../hwe')]
    ];
    return $serverList;    
}