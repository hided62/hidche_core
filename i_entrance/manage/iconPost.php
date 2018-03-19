<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터
// $_FILES['picture'] : 사진파일

$defaultImg = 'default.jpg';

$image = $_FILES['picture'];
$ext = strrchr($image['name'], ".");
$size = getImageSize($image['tmp_name']);



$imageType = $size[2];

$availableImageType = array('.jpg'=>IMAGETYPE_JPEG, '.png'=>IMAGETYPE_PNG, '.gif'=>IMAGETYPE_GIF);

$db = getRootDB();
$member = $db->queryFirstRow('SELECT `ID`, `PICTURE` FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());


$picName = $member['PICTURE'];
$newExt = array_search($imageType, $availableImageType, true);

if($picName && strlen($picName) > 11){
    $dt = substr($picName, -8);
    $picName = substr($picName, 0, -10);
}
else{
    $dt = '00000000';
}

$old_path = ROOT.'/d_pic/'.$picName;


$rf = date('Ymd');

$response['result'] = 'FAIL';
$response['msg'] = '요청이 올바르지 않습니다!';
if(!is_uploaded_file($image['tmp_name'])) {
    //진짜 전송된 파일인지 검증
    $response['msg'] = '업로드가 되지 않았습니다!';
    $response['result'] = 'FAIL';
} elseif(!$newExt) {
    //확장자 검사
    $response['msg'] = 'jpg, gif, png 파일이 아닙니다!';
    $response['result'] = 'FAIL';
} elseif($image['size'] > 10000) {
    //파일크기 검사
    $response['msg'] = '10kb 이하로 올려주세요!';
    $response['result'] = 'FAIL';
} elseif($size[0] != 64 || $size[1] != 64) {
    //이미지크기 검사
    $response['msg'] = '64x64 크기로 올려주세요!';
    $response['result'] = 'FAIL';
} elseif($dt == $rf) {
    //갱신날짜 검사
    $response['msg'] = '1일 1회 변경 가능합니다!';
    $response['result'] = 'FAIL';
} else {
    //이미지 저장

    while(true){
        $newPicName = dechex(rand(0x000000f,0xfffffff)).$newExt; 
        $dest = ROOT.'/d_pic/'.$newPicName;
        if(file_exists($dest)){
            continue;
        }
        break;
    }

    if(!move_uploaded_file($image['tmp_name'], $dest)) {
        $response['msg'] = '업로드에 실패했습니다!';
        $response['result'] = 'FAIL';
    } else {
        $pic = "{$newPicName}?={$rf}";
        getRootDB()->update('MEMBER',[
            'PICTURE' => $pic,
            'IMGSVR' => 1
        ], 'NO=%i', $SESSION->NoMember());

        //TODO: 각 서버에도 전파
        //Update('general', "PICTURE='{$pic}', IMGSVR=1", "NPC=0 AND USER_ID='{$member['ID']}'");

        $response['msg'] = '업로드에 성공했습니다!';
        $response['result'] = 'SUCCESS';
    }
}


/*
echo "<script type='text/javascript'>
    alert('{$response['msg']}');
    location.replace('".ROOT.'/i_entrance/entrance.php'."');
</script>";
*/
echo ROOT.'/i_entrance/entrance.php';//TODO:debug all and replace

