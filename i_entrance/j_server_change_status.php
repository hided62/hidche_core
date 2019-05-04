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

    if($action == 'close') { //폐쇄
        $doClose = false;
        if($userGrade >= 5){
            $doClose = true;
        }
        else if(in_array('openClose', $serverAcl)){
            $doClose = true;
        }

        if(!$doClose && in_array('reset', $serverAcl) && file_exists($serverPath.'/d_setting/DB.php')){
            require($serverPath.'/lib.php');
            $localGameStorage = KVStorage::getStorage(DB::db(), 'game_env');
            //천통 이후, 오픈 직후는 닫을 수 있음
            $localGameStorage->cacheValues(['isunited', 'startyear', 'year']);

            if($localGameStorage->isunited){
                $doClose = true;
            }
            else if($localGameStorage->year < $localGameStorage->startyear + 2){
                $doClose = true;
            }

        }

        if(!$doClose){
            if(in_array('reset', $serverAcl)){
                $response['msg'] = '서버 시작 직후, 또는 천하통일 이후에만 닫을 수 있습니다.';
            }
            else{
                $response['msg'] = '서버 닫기 권한이 부족합니다.';
            }
            return false;
        }
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
