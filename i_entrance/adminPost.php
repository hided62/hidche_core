<?php
// 외부 파라미터
// $_POST['action'] : 0: 공지, 1: 서버
// $_POST['notice'] : 공지
// $_POST['server'] : 서버 인덱스
// $_POST['select'] : 0: 폐쇄, 1: 리셋, 2: 오픈

require_once('_common.php');
require(ROOT.W.E_LIB.W.'util.php');
require(ROOT.W.E_LIB.W.'plates.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

function escapeIPv4($ip){
    return str_replace('.', '\\.', $ip);
}

$action = util::array_get($_POST['action'], '');
$notice = util::array_get($_POST['notice'], '');
$server = util::array_get($_POST['server'], '');
$select = util::array_get($_POST['select'], '');


$rs = $DB->Select('GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

function doServerModeSet($server, $select, &$reason){
    global $_serverDirs;
    $serverDir = $_serverDirs[$server];

    if($select == 0) {
        rename(realpath(dirname(__FILE__)).W.ROOT.W.$serverDir, realpath(dirname(__FILE__)).W.ROOT.W.$serverDir.'_close');
        rename(realpath(dirname(__FILE__)).W.ROOT.W.$serverDir.'_rest', realpath(dirname(__FILE__)).W.ROOT.W.$serverDir);
        $response['result'] = 'SUCCESS';
    } elseif($select == 1) {
        if(file_exists(realpath(dirname(__FILE__)).W.ROOT.W.$serverDir.'_close'.W.D_SETTING.W.SET.PHP)){
            @unlink(realpath(dirname(__FILE__)).W.ROOT.W.$serverDir.'_close'.W.D_SETTING.W.SET.PHP);
        }
        
        $response['installURL'] = ROOT.W."{$serverDir}_close/install.php";
        $response['result'] = 'SUCCESS';
    } elseif($select == 2) {
        rename(realpath(dirname(__FILE__)).W.ROOT.W.$serverDir, realpath(dirname(__FILE__)).W.ROOT.W.$serverDir."_rest");
        rename(realpath(dirname(__FILE__)).W.ROOT.W.$serverDir."_close", realpath(dirname(__FILE__)).W.ROOT.W.$serverDir);
        $response['result'] = 'SUCCESS';
    }
}

function doAdminPost($member, $action, $notice, $server, $select){
    $response['result'] = 'FAIL';
    if($member['GRADE'] < 6) {
        $response['result'] = 'FAIL';
        $response['msg'] = '운영자 권한이 없습니다.';
        return $response;
    }

    if($action == 0) {
        $DB->Update('SYSTEM', "NOTICE='{$notice}'", 'NO=1');
        $response['result'] = 'SUCCESS';
        return $response;
    } 
    
    if($action == 1) {
        $reason = '';
        if(doServerModeSet($server, $select)){
            $response['result'] = 'SUCCESS';
        }
        else{
            $response['msg'] = $reason;
        }

        return $response;
    }
    
    $response['result'] = 'FAIL';
    $response['msg'] = '알 수 없는 명령입니다.';
    return $response;

}

$response = doAdminPost($member, $action, $notice, $server, $select);

sleep(1);
echo json_encode($response);
