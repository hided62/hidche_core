<?php
namespace sammo;

// 외부 파라미터

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

$result = [];

session_write_close();

foreach (getServerConfigList() as $server) {
    list($serverKorName, $serverColor, $setting) = $server;

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
    } elseif (file_exists($serverPath.'/.htaccess')) {
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
        'color' => $serverColor 
    ]);
    $result[] = $state;
}  

Json::die($result);