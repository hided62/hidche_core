<?php
namespace sammo;

require_once('_common.php');

$session = Session::Instance()->setReadOnly();

if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'권한이 부족합니다.'
    ]);
}

$result = [];

$server = [];


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
            'reason'=>'디렉토리 없음'
        ];
    } elseif (!file_exists($serverPath.'/index.php')) {
        $state = [
            'valid'=>false,
            'run'=>false,
            'reason'=>'index.php 없음'
        ];
    } elseif (!$setting->isExists()) {
        $state = [
            'valid'=>false,
            'run'=>false,
            'reason'=>'설정 파일 없음'
        ];
    } elseif (!$setting->isRunning()) {
        // 폐쇄중
        $state = [
            'valid'=>true,
            'run'=>false,
            'reason'=>'폐쇄됨'
        ];
    } else {
        // 오픈중
        $state = [
            'valid'=>true,
            'run'=>true,
            'reason'=>'운영중'
        ];
    }

    $state = array_merge($state, [
        'name' => $serverDir,
        'korName' => $serverKorName,
        'color' => $serverColor,
    ]);
    $server[] = $state;
}  

Json::die([
    'server' => $server,
    'grade' => $session->userGrade
]);