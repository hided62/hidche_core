<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
$session = Session::requireLogin();



// 외부 파라미터
// $_POST['action'] : 'notice', 'open', 'close', 'reset', 'reset_full'
// $_POST['notice'] : 공지
// $_POST['server'] : 서버 인덱스

function escapeIPv4($ip){
    return str_replace('.', '\\.', $ip);
}

$action = Util::array_get($_POST['action'], '');
$notice = Util::array_get($_POST['notice'], '');
$server = Util::array_get($_POST['server'], '');

$db = getRootDB();
$userGrade = $session->userGrade;
session_write_close();

if($userGrade < 6) {
    Json::die([
        'result'=>'FAIL',
        'msg'=>'운영자 권한이 없습니다.'
    ]);
}

function doServerModeSet($server, $action, &$response){
    $serverList = AppConf::getList();
    $settingObj = $serverList[$server][2];

    $serverDir = $settingObj->getShortName();
    $serverPath = $settingObj->getBasePath();
    $realServerPath = realpath(dirname(__FILE__)).'/'.$serverPath;

    if($action == 'close') { //폐쇄
        $templates = new \League\Plates\Engine('templates');

        //TODO: .htaccess가 서버 오픈에도 사용될 수 있으니 별도의 방법이 필요함
        $allow_ip = Util::get_client_ip(false);
        if(Util::starts_with($allow_ip, '192.168.') ||
            Util::starts_with($allow_ip, '10.'))
        {
            //172.16~172.31은 코딩하기 귀찮으니까 안할거다
            $allow_ip = Util::get_client_ip(true);
        }

        $xforward_allow_ip = escapeIPv4($allow_ip);

        $htaccess = $templates->render('block_htaccess', 
            ['allow_ip' => $allow_ip, 'xforward_allow_ip' => $xforward_allow_ip]);
        file_put_contents($serverPath.'/.htaccess', $htaccess);
    } elseif($action == 'reset') {//리셋
        //FIXME: reset, reset_full 구현
        if(file_exists($serverPath.'/d_setting/conf.php')){
            @unlink($serverPath.'/d_setting/conf.php');
        }
        
        $response['installURL'] = $serverDir."/install.php";
    } elseif($action == 'open') {//오픈
        if(file_exists($serverPath.'/.htaccess')){
            @unlink($serverPath.'/.htaccess');
        }
    } else{
        return false;
    }
    return true;
}

function doAdminPost($action, $notice, $server){
    $response['result'] = 'FAIL';

    if($action == 'notice') {
        getRootDB()->update('SYSTEM', ['NOTICE'=>$notice], 'NO=1');
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

echo json_encode($response);
