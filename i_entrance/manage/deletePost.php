<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.'DBS'.PHP);
require_once(ROOT.W.F_CONFIG.W.SETTINGS.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

// 외부 파라미터

$respone = [];

$rs = $DB->Select('ID, PICTURE', 'MEMBER', "NO='{$SESSION->NoMember()}'");

$member = $DB->Get($rs);



$picName = $member['PICTURE'];

if($picName && strlen($picName) > 10){
    $dt = substr($picName, -8);
    $picName = substr($picName, 0, -9);
}
else{
    $dt = '00000000';
}

$dest = ROOT.W.D."pic/{$picName}";

$rf = date('Ymd');

$response['result'] = 'FAIL';
$response['msg'] = '요청이 올바르지 않습니다!';

if($dt == $rf) {
    //갱신날짜 검사
    $response['msg'] = '1일 1회 변경 가능합니다!';
    $response['result'] = 'FAIL';
} else {
    $DB->Update('MEMBER', "PICTURE='', IMGSVR=0", "NO='{$SESSION->NoMember()}'");
    if(file_exists(__DIR__.'/'.$dest)){
        @unlink(__DIR__.'/'.$dest);
    }

    for($i=0; $i < $_serverCount; $i++) {
        if($SETTINGS[$i]->IsExist()) {
            $rs = $DBS[$i]->Select('IMG', 'game', "NO='1'");
            $game = $DBS[$i]->Get($rs);
            if($game['IMG'] > 0) {
                // 엔장선택 제외하고 업데이트
                $DBS[$i]->Update('general', "PICTURE='default.jpg', IMGSVR=0", "NPC=0 AND USER_ID='{$member['ID']}'");
            }
        }
    }

    $response['msg'] = '제거에 성공했습니다!';
    $response['result'] = 'SUCCESS';
}

sleep(1);
echo json_encode($response);