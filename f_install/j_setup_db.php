<?php

namespace sammo;

require(__DIR__ . '/../vendor/autoload.php');

$host = Util::getPost('db_host');
$port = Util::getPost('db_port', 'int');
$username = Util::getPost('db_id');
$password = Util::getPost('db_pw');
$dbName = Util::getPost('db_name');
$servHost = Util::getPost('serv_host');
$sharedIconPath = Util::getPost('shared_icon_path');
$gameImagePath = Util::getPost('game_image_path');
$imageRequestKey = Util::getPost('image_request_key');

$kakaoRESTKey = Util::getPost('kakao_rest_key', 'string', '');
$kakaoAdminKey = Util::getPost('kakao_admin_key', 'string', '');


if (!$host || !$port || !$username || !$password || !$dbName || !$servHost || !$sharedIconPath || !$gameImagePath) {
    Json::die([
        'result' => false,
        'reason' => '입력 값이 올바르지 않습니다'
    ]);
}

if (!filter_var($servHost, FILTER_VALIDATE_URL)) {
    Json::die([
        'result' => false,
        'reason' => '접속 경로가 올바르지 않습니다.'
    ]);
}

if (file_exists(ROOT . '/d_setting/RootDB.php') && is_dir(ROOT . '/d_setting/RootDB.php')) {
    Json::die([
        'result' => false,
        'reason' => 'd_setting/RootDB.php 가 디렉토리입니다'
    ]);
}

if (class_exists('\\sammo\\RootDB')) {
    Json::die([
        'result' => false,
        'reason' => '이미 RootDB.php 파일이 있습니다'
    ]);
}

//파일 권한 검사
if (file_exists(AppConf::getUserIconPathFS()) && !is_dir(AppConf::getUserIconPathFS())) {
    Json::die([
        'result' => false,
        'reason' => AppConf::$userIconPath . ' 이 디렉토리가 아닙니다'
    ]);
}

if (file_exists(ROOT . '/d_log') && !is_dir(ROOT . '/d_log')) {
    Json::die([
        'result' => false,
        'reason' => 'd_log 가 디렉토리가 아닙니다'
    ]);
}

if (file_exists(ROOT . '/d_shared') && !is_dir(ROOT . '/d_shared')) {
    Json::die([
        'result' => false,
        'reason' => 'd_shared 가 디렉토리가 아닙니다'
    ]);
}

if (file_exists(ROOT . '/d_setting') && !is_dir(ROOT . '/d_setting')) {
    Json::die([
        'result' => false,
        'reason' => 'd_shared 가 디렉토리가 아닙니다'
    ]);
}

if (
    !file_exists(ROOT . '/d_log')
    || !file_exists(ROOT . '/d_shared')
    || !file_exists(ROOT . '/d_setting')
    || !file_exists(AppConf::getUserIconPathFS())
) {
    if (!is_writable(ROOT)) {
        Json::die([
            'result' => false,
            'reason' => '하위 디렉토리 생성 권한이 없습니다'
        ]);
    }

    //기본 파일 생성
    if (!file_exists(AppConf::getUserIconPathFS())) {
        mkdir(AppConf::getUserIconPathFS(), 0775);
    }

    if (!file_exists(ROOT . '/d_log')) {
        mkdir(ROOT . '/d_log', 0775);
    }

    if (!file_exists(ROOT . '/d_setting')) {
        mkdir(ROOT . '/d_setting', 0775);
    }

    if (!file_exists(ROOT . '/d_shared')) {
        mkdir(ROOT . '/d_shared', 0775);
    }
}


if (!is_writable(AppConf::getUserIconPathFS())) {
    Json::die([
        'result' => false,
        'reason' => AppConf::$userIconPath . ' 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if (!is_writable(ROOT . '/d_log')) {
    Json::die([
        'result' => false,
        'reason' => 'd_log 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if (!is_writable(ROOT . '/d_shared')) {
    Json::die([
        'result' => false,
        'reason' => 'd_shared 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if (!is_writable(ROOT . '/d_setting')) {
    Json::die([
        'result' => false,
        'reason' => 'd_setting 디렉토리의 쓰기 권한이 없습니다.'
    ]);
}



if (!file_exists(ROOT . '/d_log/.htaccess')) {
    @file_put_contents(ROOT . '/d_log/.htaccess', 'Deny from  all');
}

if (!file_exists(ROOT . '/d_setting/.htaccess')) {
    @file_put_contents(ROOT . '/d_setting/.htaccess', 'Deny from  all');
}

//DB 접근 권한 검사

$rootDB = new \MeekroDB($host, $username, $password, $dbName, $port, 'utf8mb4');
$rootDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;

$rootDB->throw_exception_on_nonsql_error = false;
$rootDB->nonsql_error_handler = function ($params) {
    Json::die([
        'result' => false,
        'reason' => 'DB 접속에 실패했습니다.'
    ]);
};

$rootDB->error_handler = function ($params) {
    Json::die([
        'result' => false,
        'reason' => 'SQL을 제대로 실행하지 못했습니다. DB상태를 확인해 주세요.'
    ]);
};

$mysqli_obj = $rootDB->get(); //로그인에 실패할 경우 자동으로 dbConnFail()이 실행됨.

if ($mysqli_obj->multi_query(file_get_contents(__DIR__ . '/sql/common_schema.sql'))) {
    while (true) {
        if (!$mysqli_obj->more_results()) {
            break;
        }
        if (!$mysqli_obj->next_result()) {
            break;
        }
    }
}

$rootDB->insert('system', array(
    'REG'     => 'N',
    'LOGIN'    => 'N',
    'CRT_DATE' => TimeUtil::now(),
    'MDF_DATE' => TimeUtil::now()
));

$globalSalt = bin2hex(random_bytes(16));

'@phan-var-force string $servHost';
'@phan-var-force string $sharedIconPath';
'@phan-var-force string $gameImagePath';

$sharedIconPath = WebUtil::resolveRelativePath($sharedIconPath, $servHost);
$gameImagePath = WebUtil::resolveRelativePath($gameImagePath, $servHost);
$imageRequestPath = WebUtil::resolveRelativePath($gameImagePath . '/../hook/git_pull.php', $servHost);
$imageKeyInstallPath = WebUtil::resolveRelativePath($gameImagePath . '/../hook/InstallKey.php', $servHost);

$result = Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/templates/ServConfig.orig.php',
    ROOT . '/d_setting/ServConfig.php',
    [
        'serverBasePath' => $servHost,
        'sharedIconPath' => $sharedIconPath,
        'gameImagePath' => $gameImagePath,
        'imageRequestPath' => $imageRequestPath,
        'imageRequestKey' => $imageRequestKey,
        'serverList' => [
            ['che', '체', 'white'],
            ['kwe', '퀘', 'yellow'],
            ['pwe', '풰', 'orange'],
            ['twe', '퉤', 'magenta'],
            ['nya', '냐', '#e67e22'],
            ['pya', '퍄', '#9b59b6']
        ]
    ],
    true
);

if ($imageRequestKey) {
    @file_get_contents($imageKeyInstallPath . '?key=' . $imageRequestKey);
}

if ($result !== true) {
    Json::die([
        'result' => false,
        'reason' => $result
    ]);
}

$result = Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/templates/common_path.orig.js',
    ROOT . '/d_shared/common_path.js',
    [
        'serverBasePath' => $servHost,
        'sharedIconPath' => $sharedIconPath,
        'gameImagePath' => $gameImagePath
    ],
    true
);

$result = Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/templates/menu.orig.json',
    ROOT . '/d_shared/menu.json',
    [],
    true
);

if ($result !== true) {
    Json::die([
        'result' => false,
        'reason' => $result
    ]);
}


$result = Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/templates/common.orig.css',
    ROOT . '/d_shared/common.css',
    [
        'serverBasePath' => $servHost,
        'sharedIconPath' => $sharedIconPath,
        'gameImagePath' => $gameImagePath
    ],
    true
);

if ($result !== true) {
    Json::die([
        'result' => false,
        'reason' => $result
    ]);
}


$result = Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/templates/RootDB.orig.php',
    ROOT . '/d_setting/RootDB.php',
    [
        'host' => $host,
        'user' => $username,
        'password' => $password,
        'dbName' => $dbName,
        'port' => $port,
        'globalSalt' => $globalSalt,
    ]
);

$kakaoRedirectURI = WebUtil::resolveRelativePath('oauth_kakao/oauth.php', $servHost . '/');

Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/templates/KakaoKey.orig.php',
    ROOT . '/d_setting/KakaoKey.php',
    [
        'REST_API_KEY' => $kakaoRESTKey,
        'ADMIN_KEY' => $kakaoAdminKey,
        'REDIRECT_URI' => $kakaoRedirectURI
    ],
    true
);

if ($result !== true) {
    Json::die([
        'result' => false,
        'reason' => $result
    ]);
}

Json::die([
    'result' => true,
    'reason' => 'success',
    'globalSalt' => $globalSalt
]);
