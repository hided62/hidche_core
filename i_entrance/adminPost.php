<?php
// 외부 파라미터
// $_POST['action'] : 0: 공지, 1: 서버
// $_POST['notice'] : 공지
// $_POST['server'] : 서버 인덱스
// $_POST['select'] : 0: 폐쇄, 1: 리셋, 2: 오픈

require_once('_common.php');
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

function doServerModeSet($server, $select, &$response){
    global $_serverDirs;
    $serverDir = $_serverDirs[$server];
    $serverPath = ROOT.W.$serverDir;
    $realServerPath = realpath(dirname(__FILE__)).W.$serverPath;

    if($select == 0) { //폐쇄
        $templates = new Plates('templates');
        

        //TODO: .htaccess가 서버 오픈에도 사용될 수 있으니 별도의 방법이 필요함
        $allow_ip = util::get_client_ip(false);
        if(util::starts_with($allow_ip, '192.168.') ||
            util::starts_with($allow_ip, '10.'))
        {
            //172.16~172.31은 코딩하기 귀찮으니까 안할거다
            $allow_ip = util::get_client_ip(true);
        }

        $xforward_allow_ip = escapeIPv4($allow_ip);

        $htaccess = $templates->render('block_htaccess', 
            ['allow_ip' => $allow_ip, 'xforward_allow_ip' => $xforward_allow_ip]);
        file_put_contents($realServerPath.'/.htaccess', $htaccess);
    } elseif($select == 1) {//리셋
        if(file_exists($realServerPath.W.D_SETTING.W.SET.PHP)){
            @unlink($realServerPath.W.D_SETTING.W.SET.PHP);
        }
        
        $response['installURL'] = $serverPath.W."install.php";
    } elseif($select == 2) {//오픈
        if(file_exists($realServerPath.'.htaccess')){
            @unlink($realServerPath.'.htaccess');
        }
    }
    return true;
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
        if(doServerModeSet($server, $select, $response)){
            $response['result'] = 'SUCCESS';
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
