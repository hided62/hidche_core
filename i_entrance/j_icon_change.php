<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();

// 외부 파라미터
// $_FILES['image_upload'] : 사진파일

$defaultImg = 'default.jpg';

$image = $_FILES['image_upload'];
$ext = strrchr($image['name'], ".");
$size = getImageSize($image['tmp_name']);



$imageType = $size[2];

$availableImageType = array('.jpg'=>IMAGETYPE_JPEG, '.png'=>IMAGETYPE_PNG, '.gif'=>IMAGETYPE_GIF);

$db = RootDB::db();
$member = $db->queryFirstRow('SELECT `ID`, `PICTURE` FROM `MEMBER` WHERE `NO` = %i', $session->userID);


$picName = $member['PICTURE'];
$newExt = array_search($imageType, $availableImageType, true);

if($picName && strlen($picName) > 11){
    $dt = substr($picName, -8);
    $picName = substr($picName, 0, -10);
}
else{
    $dt = '00000000';
}

$rf = date('Ymd');

$response['result'] = false;
$response['reason'] = '요청이 올바르지 않습니다!';
if(!is_uploaded_file($image['tmp_name'])) {
    //진짜 전송된 파일인지 검증
    $response['reason'] = '업로드가 되지 않았습니다!';
    $response['result'] = false;
} elseif(!$newExt) {
    //확장자 검사
    $response['reason'] = 'jpg, gif, png 파일이 아닙니다!';
    $response['result'] = false;
} elseif($image['size'] > 10000) {
    //파일크기 검사
    $response['reason'] = '10kb 이하로 올려주세요!';
    $response['result'] = false;
} elseif($size[0] != 64 || $size[1] != 64) {
    //이미지크기 검사
    $response['reason'] = '64x64 크기로 올려주세요!';
    $response['result'] = false;
} elseif($dt == $rf) {
    //갱신날짜 검사
    $response['reason'] = '1일 1회 변경 가능합니다!';
    $response['result'] = false;
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
        $response['reason'] = '업로드에 실패했습니다!';
        $response['result'] = false;
    } else {
        $pic = "{$newPicName}?={$rf}";
        RootDB::db()->update('MEMBER',[
            'PICTURE' => $pic,
            'IMGSVR' => 1
        ], 'NO=%i', $session->userID);

        $servers = [];

        foreach(AppConf::getList() as $key=>$setting){
            if($setting->isRunning()){
                $servers[] = $key;
            }
        }

        $response['servers'] = $servers;

        $response['reason'] = '업로드에 성공했습니다!';
        $response['result'] = true;
    }
}


Json::die($response);