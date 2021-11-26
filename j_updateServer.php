<?php

namespace sammo;

require(__DIR__ . '/vendor/autoload.php');

set_time_limit(600);

function getVersion($target = null)
{
    $versionTokens = [];

    if ($target) {
        $command = sprintf('git describe %s --long --tags', escapeshellarg($target));
    } else {
        $command = 'git describe --long --tags';
    }
    exec($command, $output);
    if (is_array($output)) {
        $versionTokens[] = trim(join('', $output));
    }

    if ($target) {
        $command = sprintf('git branch --contains %s', escapeshellarg($target));
    } else {
        $command = 'git branch --contains HEAD';
    }
    exec($command, $output);
    if (is_array($output)) {
        if (count($output)) {
            $output = Util::array_last($output);
            $output = trim($output, " \t\n\r\0\x0b*");
            $output = explode('/', $output);
            $versionTokens[] = Util::array_last($output);
        } else {
            $versionTokens[] = 'unknown';
        }
    }

    return join('-', $versionTokens);
}

function getHash($target = 'HEAD')
{
    $command = sprintf('git rev-parse %s', escapeshellarg($target));
    exec($command, $output);
    if (is_array($output)) {
        $output = join('', $output);
    }
    return trim($output);
}

function genJS($server)
{
    $command = sprintf("./node_modules/.bin/webpack build --env target=%s", escapeshellarg($server));

    exec(($command), $output, $result_code);
    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output
        ]);
    }
}

function tryNpmInstall()
{
    $npmResultPath = './npm_recent.json.log';
    $packageJsonPath = './package.json';
    $packageJsonLockPath = './package-lock.json';

    $packageJsonHash = hash_file('sha512', $packageJsonPath);
    $timestamp = time();
    if (file_exists($npmResultPath) && file_exists($packageJsonLockPath)) {
        do {
            $result = json_decode(file_get_contents($npmResultPath));
            $oldJsonHash = $result->packageJsonHash;
            $oldTimestamp = $result->updateTimestamp;

            //1. package.json 파일이 다르면 업데이트.
            if ($packageJsonHash != $oldJsonHash) {
                break;
            }

            //2. package-lock.json 업데이트가 2주를 초과했다면 업데이트.
            if ($oldTimestamp + 60 * 60 * 24 * 14 < $timestamp) {
                break;
            }

            //그것도 아니라면 업데이트하지 않겠다.
            return false;
        } while (0);
    }

    exec("npm install", $output, $result_code);
    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output
        ]);
    }

    file_put_contents($npmResultPath, json_encode([
        'packageJsonHash' => $packageJsonHash,
        'updateTimestamp' => $timestamp,
    ]));
    return true;
}

//묻고 따지지 않고 일단 npm install은 시도한다.
//hwe 업데이트인 경우에만 한번 더 부른다.

if (tryNpmInstall()) {
    genJS(Util::array_last_key(ServConfig::getServerList()));
}
$session = Session::requireLogin(null)->setReadOnly();

$request = $_POST + $_GET;

$rootDB = RootDB::db();
$storage = new \sammo\KVStorage($rootDB, 'git_path');
$tmpFile = 'd_log/arc.zip';

$v = new Validator($request);

$v->rule('required', [
    'server'
])->rule('regex', 'target', '/^[0-9a-zA-Z^{}\\/\-_,.@]+$/');

if (!$v->validate()) {
    Json::die([
        'result' => false,
        'reason' => $v->errorStr()
    ]);
}

$target = Util::getPost('target');

$server = basename($request['server']);

$allowFullUpdate = in_array('fullUpdate', $session->acl[$server] ?? []);
$allowFullUpdate |= $session->userGrade >= 6;

$allowUpdate = in_array('update', $session->acl[$server] ?? []);
$allowUpdate |= $session->userGrade >= 5;
$allowUpdate |= $allowFullUpdate;


if (!$allowUpdate) {
    Json::die([
        'result' => false,
        'reason' => '권한이 충분하지 않습니다'
    ]);
}

$src_target = $storage->$server;
if ($src_target) {
    $src_target = $src_target[0];
}

if (!$allowFullUpdate || !$target) {
    $target = $src_target;
} else {
    $target = $request['target'];
}

$baseServerName = Util::array_last_key(ServConfig::getServerList());

if (!$target && $server != $baseServerName) {
    Json::die([
        'result' => false,
        'reason' => 'git -ish target이 제대로 지정되지 않았습니다.'
    ]);
}


$targetDir = $target . ':' . $baseServerName;

if (!key_exists($server, ServConfig::getServerList())) {
    Json::die([
        'result' => false,
        'reason' => '불가능한 서버 이름입니다.'
    ]);
}

if (\file_exists($server) && !is_dir($server)) {
    Json::die([
        'result' => false,
        'reason' => '같은 이름을 가진 파일이 있습니다.'
    ]);
}

if (file_exists($server) && !is_writable($server)) {
    Json::die([
        'result' => false,
        'reason' => $server . ' 디렉토리 쓰기 권한이 없습니다.'
    ]);
}

if (!file_exists($server)) {
    if (!is_writable('.')) {
        Json::die([
            'result' => false,
            'reason' => $server . ' 디렉토리가 없지만 생성할 권한이 없습니다.'
        ]);
    }
    mkdir($server, 0755);
}


if ($server == $baseServerName) {

    exec("git fetch -q 2>&1", $output);
    if ($output) {
        Json::die([
            'result' => false,
            'reason' => 'git pull 작업 : ' . join(', ', $output)
        ]);
    }

    if ($target != $src_target) {
        $command = sprintf('git checkout %s -q 2>&1', $target);
        exec($command, $output);
        if ($output) {
            Json::die([
                'result' => false,
                'reason' => join(', ', $output)
            ]);
        }
    }

    exec("git pull -q 2>&1", $output);
    if ($output && $output[0] != 'Already up-to-date.') {
        Json::die([
            'result' => false,
            'reason' => 'git pull 작업 : ' . join(', ', $output)
        ]);
    }


    $version = getVersion();
    $gitHash = getHash();
    if (
        hash_file("sha256", __DIR__ . '/' . $server . '/d_setting/VersionGit.dynamic.orig.php') ==
        hash_file("sha256", __DIR__ . '/' . $server . '/d_setting/VersionGit.php')
    ) {
        $result = true;
    } else {
        $result = Util::generateFileUsingSimpleTemplate(
            __DIR__ . '/' . $server . '/d_setting/VersionGit.orig.php',
            __DIR__ . '/' . $server . '/d_setting/VersionGit.php',
            [
                'verionGit' => $version,
                'hash' => $gitHash
            ],
            true
        );
    }

    //git 업데이트했는데, package.json이 바뀌면 곤란하니까
    tryNpmInstall();
    genJS($server);

    if (ServConfig::$imageRequestKey) {
        try {
            $imagePullPath = ServConfig::getImagePullURI();
            $pullResult = @file_get_contents($imagePullPath);
            if ($pullResult === false) {
                throw new \ErrorException('Invalid URI');
            }
            $pullResult = Json::decode($pullResult);
            if ($pullResult['result']) {
                $imgResult = true;
                $imgDetail = $pullResult['version'];
            } else {
                $imgResult = false;
                $imgDetail = $pullResult['reason'];
            }
        } catch (\Exception $e) {
            $imgResult = false;
            $imgDetail = $e->getMessage();
        }
    } else {
        $imgResult = true;
        $imgDetail = 'No key';
    }



    $storage->$server = [$target, $version];
    //opcache_reset();

    Json::die([
        'server' => $server,
        'result' => true,
        'version' => $version,
        'hash' => $gitHash,
        'imgResult' => $imgResult,
        'imgDetail' => $imgDetail,
    ]);
}

$command = sprintf('git archive --format=zip -o %s %s', escapeshellarg($tmpFile), escapeshellarg($targetDir));
exec("$command 2>&1", $output);
if ($output) {
    Json::die([
        'result' => false,
        'reason' => join(', ', $output)
    ]);
}

$zip = new \ZipArchive;
if ($zip->open($tmpFile) !== true) {
    Json::die([
        'result' => false,
        'reason' => 'archive가 생성되지 않았습니다.'
    ]);
}

if (!$zip->extractTo($server)) {
    Json::die([
        'result' => false,
        'reason' => '생성한 archive를 제대로 옮기지 못했습니다.'
    ]);
}

$zip->close();
@unlink($tmpFile);

$version = getVersion($target);
$gitHash = getHash($target);
$result = Util::generateFileUsingSimpleTemplate(
    __DIR__ . '/' . $server . '/d_setting/VersionGit.orig.php',
    __DIR__ . '/' . $server . '/d_setting/VersionGit.php',
    [
        'verionGit' => $version,
        'hash' => $gitHash
    ],
    true
);
genJS($server);

$storage->$server = [$target, $version];
//ServConfig::getServerList()[$server]->closeServer();
//opcache_reset();

Json::die([
    'server' => $server,
    'result' => true,
    'version' => $version,
    'hash' => $gitHash,
    'imgResult' => false,
]);
