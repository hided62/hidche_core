<?php

require('_common.php');
require(__DIR__.'/../f_config/SETTING.php');
use utilphp\util as util;


function dbConnFail($params){
    returnJson([
        'step'=>'conn_fail'
    ]);
}

function dbSQLFail($params){
    returnJson([
        'step'=>'sql_fail'
    ]);
}

if(!$SETTING->isExist()){
    returnJson([
        'step'=>'config'
    ]);
}

require(__DIR__.'/../f_config/DB.php');
$rootDB = getRootDB();

$rootDB->throw_exception_on_nonsql_error = false;
$rootDB->nonsql_error_handler = 'dbConnFail';
$rootDB->error_handler = 'dbSQLFail';

$memberCnt = $rootDB->queryFirstField('SELECT count(`NO`) from MEMBER');
if($memberCnt === 0){
    returnJson([
        'step'=>'admin',
        'globalSalt'=>getGlobalSalt()
    ]);
}


returnJson([
    'step'=>'done'
]);