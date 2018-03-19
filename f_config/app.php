<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Setting.php');


function getServerConfigList(){
    static $serverList = null;
    if($serverList !== null){
        return $serverList;
    }
    $serverList = [
        'che'=>['체', 'white', new _Setting(__DIR__.'/../che')],
        'kwe'=>['퀘', 'yellow', new _Setting(__DIR__.'/../kwe')],
        'pwe'=>['풰', 'orange', new _Setting(__DIR__.'/../pwe')],
        'twe'=>['퉤', 'magenta', new _Setting(__DIR__.'/../twe')],
        'hwe'=>['훼', 'red', new _Setting(__DIR__.'/../hwe')]
    ];
    return $serverList;    
}