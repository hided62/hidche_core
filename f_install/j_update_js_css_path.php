<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 6){
    Json::die([
        'result'=>false,
        'reason'=>'관리자 아님'
    ]);
}

$servHost = Util::getReq('serv_host');
$sharedIconPath = Util::getReq('shared_icon_path');
$gameImagePath = Util::getReq('game_image_path');

if($servHost){
    if(!$sharedIconPath || $gameImagePath){
        Json::die([
            'result'=>false,
            'reason'=>'serv_host가 지정된 경우, sharedIconPath와 gameImagePath가 모두 지정되어야합니다.'
        ]);
    }

    $sharedIconPath = WebUtil::resolveRelativePath($sharedIconPath, $servHost);
    $gameImagePath = WebUtil::resolveRelativePath($gameImagePath, $servHost);

    $result = Util::generateFileUsingSimpleTemplate(
        __DIR__.'/templates/ServConfig.orig.php',
        ROOT.'/d_setting/ServConfig.php',
        [
            'serverBasePath'=>$servHost,
            'sharedIconPath'=>$sharedIconPath,
            'gameImagePath'=>$gameImagePath
        ],
        true
    );

    if ($result !== true) {
        Json::die([
            'result'=>false,
            'reason'=>$result
        ]);
    }
}
else if($sharedIconPath || $gameImagePath){
    $servHost = ServConfig::$serverWebPath;
    if($sharedIconPath){
        $sharedIconPath = WebUtil::resolveRelativePath($sharedIconPath, $servHost);
    }
    else{
        $sharedIconPath = ServConfig::$sharedIconPath;
    }

    if($gameImagePath){
        $gameImagePath = WebUtil::resolveRelativePath($gameImagePath, $servHost);
    }
    else{
        $gameImagePath = ServConfig::$gameImagePath;
    }

    $result = Util::generateFileUsingSimpleTemplate(
        __DIR__.'/templates/ServConfig.orig.php',
        ROOT.'/d_setting/ServConfig.php',
        [
            'serverBasePath'=>$servHost,
            'sharedIconPath'=>$sharedIconPath,
            'gameImagePath'=>$gameImagePath
        ],
        true
    );

    if ($result !== true) {
        Json::die([
            'result'=>false,
            'reason'=>$result
        ]);
    }
}
else{
    $servHost = ServConfig::$serverWebPath;
    $sharedIconPath = ServConfig::$sharedIconPath;
    $gameImagePath = ServConfig::$gameImagePath;
}



$result = Util::generateFileUsingSimpleTemplate(
    __DIR__.'/templates/common_path.orig.js',
    ROOT.'/d_shared/common_path.js',
    [
        'serverBasePath'=>$servHost,
        'sharedIconPath'=>$sharedIconPath,
        'gameImagePath'=>$gameImagePath
    ],
    true
);


$result = Util::generateFileUsingSimpleTemplate(
    __DIR__.'/templates/common.orig.css',
    ROOT.'/d_shared/common.css',
    [
        'serverBasePath'=>$servHost,
        'sharedIconPath'=>$sharedIconPath,
        'gameImagePath'=>$gameImagePath
    ],
    true
);

if ($result !== true) {
    Json::die([
        'result'=>false,
        'reason'=>$result
    ]);
}
