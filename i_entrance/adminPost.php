<?php
// 외부 파라미터
// $_POST['action'] : 0: 공지, 1: 서버
// $_POST['notice'] : 공지
// $_POST['server'] : 서버 인덱스
// $_POST['select'] : 0: 폐쇄, 1: 리셋, 2: 오픈

require_once('_common.php');
require_once(ROOT.W.E_LIB.W.'util.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

function getPost($str){
    if(!isset($_POST[$str])){
        return '';
    }
    $temp = $_POST[$str];
    $temp=str_replace("　","",$temp);
    $temp=str_replace("\n","",$temp);
    $temp=strip_tags($temp);
    $temp=str_replace("&nbsp;","",$temp);
    $temp=str_replace(" ","",$temp);
    return $temp;
}

$action = util::array_get($_POST['action'], '');
$notice = util::array_get($_POST['notice'], '');
$server = util::array_get($_POST['server'], '');
$select = util::array_get($_POST['select'], '');


$rs = $DB->Select('GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

$response['result'] = 'FAIL';
if($member['GRADE'] < 6) {
    $response['result'] = 'FAIL';
    $response['msg'] = '운영자 권한이 없습니다.';
} else {
    if($action == 0) {
        $DB->Update('SYSTEM', "NOTICE='{$notice}'", 'NO=1');
        $response['result'] = 'SUCCESS';
    } elseif($action == 1) {
        $serverDir = $_serverDirs[$server];

        if($select == 0) {
            rename(ROOT.W.$serverDir, ROOT.W.$serverDir.'_close');
            rename(ROOT.W.$serverDir.'_rest', ROOT.W.$serverDir);
            $response['result'] = 'SUCCESS';
        } elseif($select == 1) {
            if(file_exists(ROOT.W.$serverDir.'_close'.W.D_SETTING.W.SET.PHP)){
                @unlink(ROOT.W.$serverDir.'_close'.W.D_SETTING.W.SET.PHP);
            }
            
            $response['installURL'] = ROOT.W."{$serverDir}_close/install.php";
            $response['result'] = 'SUCCESS';
        } elseif($select == 2) {
            rename(ROOT.W.$serverDir, ROOT.W.$serverDir."_rest");
            rename(ROOT.W.$serverDir."_close", ROOT.W.$serverDir);
            $response['result'] = 'SUCCESS';
        }
    } else {
        $response['result'] = 'FAIL';
        $response['msg'] = '알 수 없는 명령입니다.';
    }
}

sleep(1);
echo json_encode($response);
