<?php
namespace sammo;


require('_common.php');

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

if(!AppConf::getRoot()->isExists()){
    Json::die([
        'step'=>'config'
    ]);
}

require(__DIR__.'/../f_config/DB.php');
$rootDB = RootDB::db();

$rootDB->throw_exception_on_nonsql_error = false;
$rootDB->nonsql_error_handler = 'dbConnFail';
$rootDB->error_handler = 'dbSQLFail';

$memberCnt = $rootDB->queryFirstField('SELECT count(`NO`) from MEMBER');
if($memberCnt === 0){
    Json::die([
        'step'=>'admin',
        'globalSalt'=>RootDB::getGlobalSalt()
    ]);
}


Json::die([
    'step'=>'done'
]);