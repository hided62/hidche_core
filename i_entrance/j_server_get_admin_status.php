<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 5 && !$session->acl){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다.'
    ]);
}

$result = [];

$server = [];

$storage = new \sammo\KVStorage(RootDB::db(), 'git_path');
$serverGitPath = $storage->getAll();

$rootServer = Util::array_last(AppConf::getList())->getShortName();

foreach (AppConf::getList() as $setting) {
    $serverColor = $setting->getColor();
    $serverKorName = $setting->getKorName();

    $serverPath = $setting->getBasePath();
    $serverDir = $setting->getShortName();
    //TODO: .htaccess가 서버 오픈에도 사용될 수 있으니 별도의 방법이 필요함
    if (!is_dir($serverPath)) {
        $state = [
            'valid'=>false,
            'run'=>false,
            'installed'=>false,
            'version'=>$setting->getVersion(),
            'reason'=>'디렉토리 없음'
        ];
    } elseif (!file_exists($serverPath.'/index.php')) {
        $state = [
            'valid'=>false,
            'run'=>false,
            'installed'=>false,
            'version'=>$setting->getVersion(),
            'reason'=>'index.php 없음'
        ];
    } elseif (!$setting->isExists()) {
        $state = [
            'valid'=>false,
            'run'=>false,
            'installed'=>true,
            'version'=>$setting->getVersion(),
            'reason'=>'설정 파일 없음'
        ];
    } elseif (!$setting->isRunning()) {
        // 폐쇄중
        $state = [
            'valid'=>true,
            'run'=>false,
            'installed'=>true,
            'version'=>$setting->getVersion(),
            'reason'=>'폐쇄됨'
        ];
    } else {
        // 오픈중
        $state = [
            'valid'=>true,
            'run'=>true,
            'installed'=>true,
            'version'=>$setting->getVersion(),
            'reason'=>'운영중'
        ];
    }

    $state += [
        'name' => $serverDir,
        'korName' => $serverKorName,
        'color' => $serverColor,
        'isRoot' => $serverDir == $rootServer,
        'lastGitPath' => ($serverGitPath[$serverDir][0])??($serverDir == $rootServer?'devel':'origin/devel')
    ];
    $server[] = $state;
}  

Json::die([
    'acl' => $session->acl,
    'server' => $server,
    'grade' => $session->userGrade
]);