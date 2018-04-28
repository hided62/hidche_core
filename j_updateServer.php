<?php
namespace sammo;

require(__dir__.'/vendor/autoload.php');

function getVersion($target=null){
    if($target){
        $command = sprintf('git describe %s --long --tags', escapeshellarg($target));
    }
    else{
        $command = 'git describe --long --tags';
    }
    exec($command, $output);
    if(is_array($output)){
        $output = join('', $output);
    }
    return trim($output);
    
}

$session = Session::requireLogin(null)->setReadOnly();
if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 충분하지 않습니다'
    ]);
}

$request = $_POST + $_GET;

$rootDB = RootDB::db();
$tmpFile = 'd_log/arc.zip';

$v = new Validator($request);

$v->rule('required', [
    'server'
])->rule('regex', 'target', '/^[0-9a-zA-Z^{}\\/\-_,.@]+$/');

if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr()
    ]);
}

$target = Util::getReq('target');

$server = basename($request['server']);
if($session->userGrade <= 5 || !$target){
    $target = Json::decode($rootDB->queryFirstField('SELECT `key`=%s FROM `config`', "git_path_$server"));
    if($target){
        $target = $target[0];
    }
}
else{
    $target = $request['target'];
}

if(!$target){
    Json::die([
        'result'=>false,
        'reason'=>'git -ish target이 제대로 지정되지 않았습니다.'
    ]);
}


$baseServerName = Util::array_last_key(AppConf::getList());

$targetDir =$target.':'.$baseServerName;

if(!key_exists($server, AppConf::getList())){
    Json::die([
        'result'=>false,
        'reason'=>'불가능한 서버 이름입니다.'
    ]);
}

if(\file_exists($server) && !is_dir($server)){
    Json::die([
        'result'=>false,
        'reason'=>'같은 이름을 가진 파일이 있습니다.'
    ]);
}

if(file_exists($server) && !is_writable($server)){
    Json::die([
        'result'=>false,
        'reason'=>$server.' 디렉토리 쓰기 권한이 없습니다.'
    ]);
}

if(!file_exists($server)){
    if(!is_writable('.')){
        Json::die([
            'result'=>false,
            'reason'=>$server.' 디렉토리가 없지만 생성할 권한이 없습니다.'
        ]);
    }
    mkdir($server, 0755);
}


if($server == $baseServerName){
    exec("git pull -q 2>&1", $output);
    if($output && $output[0] != 'Already up-to-date.'){
        Json::die([
            'result'=>false,
            'reason'=>'git pull 작업 : '.join(', ', $output)
        ]);
    }

    $version = getVersion();
    $result = Util::generateFileUsingSimpleTemplate(
        __DIR__.'/'.$server.'/d_setting/VersionGit.orig.php',
        __DIR__.'/'.$server.'/d_setting/VersionGit.php',[
            'verionGit'=>$version
        ], true
    );

    Json::die([
        'server'=>$server,
        'result'=>true,
        'version'=>$version
    ]);

}

$command = sprintf('git archive --format=zip -o %s %s', escapeshellarg($tmpFile), escapeshellarg($targetDir));
exec("$command 2>&1", $output);
if($output){
    Json::die([
        'result'=>false,
        'reason'=>join(', ', $output)
    ]);
}

$zip = new \ZipArchive;
if($zip->open($tmpFile) !== true){
    Json::die([
        'result'=>false,
        'reason'=>'archive가 생성되지 않았습니다.'
    ]);
}

if(!$zip->extractTo($server)){
    Json::die([
        'result'=>false,
        'reason'=>'생성한 archive를 제대로 옮기지 못했습니다.'
    ]);
}

$zip->close();
@unlink($tmpFile);

$version = getVersion($target);
$result = Util::generateFileUsingSimpleTemplate(
    __DIR__.'/'.$server.'/d_setting/VersionGit.orig.php',
    __DIR__.'/'.$server.'/d_setting/VersionGit.php',[
        'verionGit'=>$version
    ], true
);

$rootDB->insertUpdate('config', [
    "key" => "git_path_$server",
    "value" =>Json::encode([$target, $version])
]);

//AppConf::getList()[$server]->closeServer();

Json::die([
    'server'=>$server,
    'result'=>true,
    'version'=>$version
]);