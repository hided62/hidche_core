<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터
// $_POST['select'] : 처리종류
// $_POST['no'] : NO
$select = $_POST['select'];
$no = $_POST['no'];

$db = getRootDB();
$member = $db->queryFirstRow('SELECT `GRADE` FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

if($member['GRADE'] < 6) {
    $response['result'] = 'FAIL';
    $response['msg'] = '운영자 권한이 없습니다.';
} else {
    
    if($select >= 0 && $select <= 4) {
        /* 
        0: 블럭회원
        1: 일반회원
        2: 참여회원?
        3: 유효회원?
        4: 특별회원?
        */
        $db->update('MEMBER', ['GRADE'=>$select], 'NO=%i', $no);
    } elseif($select == 5) {
        // 전콘제거
        $db->update('MEMBER', [
            'PICTURE'=>'',
            'IMGSVR'=>0
        ], 'NO=%i', $no);

    } elseif($select == 6) {
        // 비번초기화
        $pw = md5('11111111');
        $db->update('MEMBER', ['PW'=>$pw], 'NO=%i',$no);
    } elseif($select == 7) {
        // 회원삭제
        $db->delete('MEMBER', 'NO=%i', $no);
    } elseif($select == 8) {
        // 가입허용
        $db->update('SYSTEM', ['REG'=>'Y'], 'NO=1');
    } elseif($select == 9) {
        // 가입금지
        $db->update('SYSTEM', ['REG'=>'N'], 'NO=1');
    } elseif($select == 10) {
        // 로그인허용
        $db->update('SYSTEM', ['LOGIN'=>'Y'], 'NO=1');
    } elseif($select == 11) {
        // 로그인금지
        $db->update('SYSTEM', ['LOGIN'=>'N'], 'NO=1');
    } elseif($select == 12) {
        // 탈퇴처리(1개월)
        $db->delete('MEMBER', "QUIT='Y' AND GRADE<'5' AND REG_DATE<DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    } elseif($select == 13) {
        // 오래된계정(6개월)
        $db->delete('MEMBER', "GRADE<'5' AND REG_DATE<DATE_SUB(NOW(), INTERVAL 6 MONTH)");
    }

    $response['result'] = 'SUCCESS';
}

returnJson($response);


