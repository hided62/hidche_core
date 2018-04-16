<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin(null);



// 외부 파라미터
// $_POST['action'] : 'notice', 'open', 'close', 'reset', 'reset_full'
// $_POST['notice'] : 공지
// $_POST['server'] : 서버 인덱스

$action = Util::getReq('action', 'string', '');
$notice = Util::getReq('notice', 'string', '');
$server = Util::getReq('server', 'string', '');

$db = RootDB::db();
$userGrade = $session->userGrade;
$session->setReadOnly();

if($userGrade < 6) {
    Json::die([
        'result'=>'FAIL',
        'msg'=>'운영자 권한이 없습니다.'
    ]);
}

function doServerModeSet($server, $action, &$response){
    $serverList = AppConf::getList();
    $settingObj = $serverList[$server];

    $serverDir = $settingObj->getShortName();
    $serverPath = $settingObj->getBasePath();
    $realServerPath = realpath(dirname(__FILE__)).'/'.$serverPath;

    if($action == 'close') { //폐쇄
        return $settingObj->closeServer();
    } elseif($action == 'reset') {//리셋
        //FIXME: reset, reset_full 구현
        if(file_exists($serverPath.'/d_setting/DB.php')){
            @unlink($serverPath.'/d_setting/DB.php');
        }
        
        $response['installURL'] = $serverDir."/install.php";
    } elseif($action == 'open') {//오픈
        return $settingObj->openServer();
    } else{
        return false;
    }
    return true;
}

function doAdminPost($action, $notice, $server){
    $response = ['result' => 'FAIL'];

    if($action == 'notice') {
        RootDB::db()->update('system', ['NOTICE'=>$notice], true);
        $response['result'] = 'SUCCESS';
        return $response;
    } 
    
    if(doServerModeSet($server, $action, $response)){
        $response['result'] = 'SUCCESS';
        return $response;
    }

    return $response;

}

$response = doAdminPost($action, $notice, $server);

Json::die($response);
