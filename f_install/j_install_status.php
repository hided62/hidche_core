<?php
namespace sammo;


require('_common.php');
require(__DIR__.'/../f_config/SETTING.php');



function dbConnFail($params){
    Json::die([
        'step'=>'conn_fail'
    ]);
}

function dbSQLFail($params){
    Json::die([
        'step'=>'sql_fail'
    ]);
}

if(!$SETTING->isExists()){
    Json::die([
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
    Json::die([
        'step'=>'admin',
        'globalSalt'=>getGlobalSalt()
    ]);
}


Json::die([
    'step'=>'done'
]);