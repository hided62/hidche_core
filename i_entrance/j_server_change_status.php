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
$acl = $session->acl;
$session->setReadOnly();

if($userGrade < 5 && !$acl) {
    Json::die([
        'result'=>'FAIL',
        'msg'=>'운영자 권한이 없습니다.'
    ]);
}

function doServerModeSet($server, $action, &$response, $session){
    
    $serverList = AppConf::getList();
    $settingObj = $serverList[$server];
    $serverAcl = $session->acl[$server]??[];
    $userGrade = $session->userGrade;

    $serverDir = $settingObj->getShortName();
    $serverPath = $settingObj->getBasePath();
    $realServerPath = realpath(dirname(__FILE__)).'/'.$serverPath;

    if($action == 'close' && ($userGrade >= 5 || in_array('openClose', $serverAcl))) { //폐쇄
        return $settingObj->closeServer();
    } elseif($action == 'reset' && $userGrade >= 6) {//리셋
        //FIXME: reset, reset_full 구현
        if(file_exists($serverPath.'/d_setting/DB.php')){
            @unlink($serverPath.'/d_setting/DB.php');
        }
        
        $response['installURL'] = $serverDir."/install.php";
    } elseif($action == 'open' && ($userGrade >= 5 || in_array('openClose', $serverAcl))) {//오픈
        return $settingObj->openServer();
    } else{
        $response['msg'] = '올바르지 않은 요청입니다';
        return false;
    }
    return true;
}

function doAdminPost($action, $notice, $server, $session){
    $response = ['result' => 'FAIL'];

    $globalAcl = $session->acl['global']??[];
    $userGrade = $session->userGrade;

    if($action == 'notice' && ($userGrade >= 5 || in_array('notice', $globalAcl))) {
        RootDB::db()->update('system', ['NOTICE'=>$notice], true);
        $response['result'] = 'SUCCESS';
        return $response;
    } 
    
    if(doServerModeSet($server, $action, $response, $session)){
        $response['result'] = 'SUCCESS';
        return $response;
    }

    return $response;

}

$response = doAdminPost($action, $notice, $server, $session);

Json::die($response);
