<?php
namespace sammo;

require('_common.php');

$session = Session::requireLogin(null)->setReadOnly();
if($session->userGrade < 6){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 충분하지 않습니다'
    ]);
}

$request = array_merge($_POST, $_GET);

$tmpFile = 'd_log/arc.zip';

$v = new Validator($request);
$v->rule('required', [
    'server',
    'target'
])->rule('regex', 'target', '/^[0-9a-zA-Z^{}\\/\-_,.@]+$/');

if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr()
    ]);
}

$server = basename($request['server']);
$target = $request['target'];
$baseServerName = end(array_keys(AppConf::getList()));

$targetDir =$target.':'.$baseServerName;

if($server == $baseServerName || !key_exists($server, AppConf::getList())){
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
    mkdir($server, 0644);
}

exec("git fetch 2>&1", $output);
if($output){
    Json::die([
        'result'=>false,
        'reason'=>'git fetch 작업 : '.join(', ', $output)
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



//TODO: 버전명을 기록.

Json::die([
    'result'=>true,
]);