<?php

namespace sammo;

if(!file_exists(__DIR__ . '/../vendor/autoload.php')){
    http_response_code(503);
    die('not_ready');
}

require(__DIR__ . '/../vendor/autoload.php');

if (!class_exists('\\sammo\\RootDB')) {
    Json::die([
        'step' => 'config'
    ]);
}

$rootDB = RootDB::db();

$rootDB->throw_exception_on_nonsql_error = false;
$rootDB->nonsql_error_handler = function ($params) {
    Json::die([
        'step' => 'conn_fail'
    ]);
};

$rootDB->error_handler = function ($params) {
    Json::die([
        'step' => 'sql_fail'
    ]);
};

$memberCnt = $rootDB->queryFirstField('SELECT count(`NO`) from member');
if ($memberCnt == 0) {
    Json::die([
        'step' => 'admin',
        'globalSalt' => RootDB::getGlobalSalt()
    ]);
}


Json::die([
    'step' => 'done'
]);
