<?php
// 외부 파라미터
// $_POST['select'] : 처리종류
// $_POST['no'] : NO
$select = $_POST['select'];
$no = $_POST['no'];

require('_common.php');
require(ROOT.W.F_FUNC.W.'class._JSON.php');
require(ROOT.W.F_CONFIG.W.DB.PHP);
require(ROOT.W.F_CONFIG.W.'DBS'.PHP);
require(ROOT.W.F_CONFIG.W.SETTINGS.PHP);
require(ROOT.W.F_CONFIG.W.SESSION.PHP);

$rs = $DB->Select('GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

if($member['GRADE'] < 6) {
    $response['result'] = 'FAIL';
    $response['msg'] = '운영자 권한이 없습니다.';
} else {
    $rs = $DB->Select('ID', 'MEMBER', "NO='{$no}'");
    $member = $DB->Get($rs);

    if($select == 0) {
        // 블럭회원
        $DB->Update('MEMBER', 'GRADE=0', "NO='{$no}'");

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', 'USERLEVEL=0', "USER_ID='{$member['ID']}'");
            }
        }
    } elseif($select == 1) {
        // 일반회원
        $DB->Update('MEMBER', 'GRADE=1', "NO='{$no}'");

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', 'USERLEVEL=1', "USER_ID='{$member['ID']}'");
            }
        }
    } elseif($select == 2) {
        // 참여회원
        $DB->Update('MEMBER', 'GRADE=2', "NO='{$no}'");

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', 'USERLEVEL=2', "USER_ID='{$member['ID']}'");
            }
        }
    } elseif($select == 3) {
        // 유효회원
        $DB->Update('MEMBER', 'GRADE=3', "NO='{$no}'");

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', 'USERLEVEL=3', "USER_ID='{$member['ID']}'");
            }
        }
    } elseif($select == 4) {
        // 특별회원
        $DB->Update('MEMBER', 'GRADE=4', "NO='{$no}'");

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', 'USERLEVEL=4', "USER_ID='{$member['ID']}'");
            }
        }
    } elseif($select == 5) {
        // 전콘제거
        $DB->Update('MEMBER', "PICTURE='', IMGSVR=0", "NO='{$no}'");

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', "PICTURE='default.jpg', IMGSVR=0", "NPC=0 AND USER_ID='{$member['ID']}'");
            }
        }
    } elseif($select == 6) {
        // 비번초기화
        $pw = md5('11111111');
        $DB->Update('MEMBER', "PW='{$pw}'", "NO='{$no}'");
    } elseif($select == 7) {
        // 회원삭제
        $DB->Delete('MEMBER', "NO='{$no}'");
    } elseif($select == 8) {
        // 가입허용
        $DB->Update('SYSTEM', "REG='Y'", "NO='1'");
    } elseif($select == 9) {
        // 가입금지
        $DB->Update('SYSTEM', "REG='N'", "NO='1'");
    } elseif($select == 10) {
        // 로그인허용
        $DB->Update('SYSTEM', "LOGIN='Y'", "NO='1'");
    } elseif($select == 11) {
        // 로그인금지
        $DB->Update('SYSTEM', "LOGIN='N'", "NO='1'");
    } elseif($select == 12) {
        // 탈퇴처리(1개월)
        $DB->Delete('MEMBER', "QUIT='Y' AND GRADE<'5' AND REG_DATE<DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    } elseif($select == 13) {
        // 오래된계정(6개월)
        $DB->Delete('MEMBER', "GRADE<'5' AND REG_DATE<DATE_SUB(NOW(), INTERVAL 6 MONTH)");
    } elseif($select == 14) {
        // 이미지업로드
        $DB->Update('MEMBER', 'IMGSVR=0');

        for($i=0; $i < $_serverCount; $i++) {
            if($SETTINGS[$i]->IsExist()) {
                $DBS[$i]->Update('general', 'IMGSVR=0');
            }
        }
    }

    $response['result'] = 'SUCCESS';
}

sleep(1);
echo json_encode($response);


