<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

WebUtil::requireAJAX();

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

// 외부 파라미터
// $_FILES['image_upload'] : 사진파일

$defaultImg = 'default.jpg';

$image = $_FILES['image_upload'];
$ext = strrchr($image['name'], ".");
$size = getImageSize($image['tmp_name']);



$imageType = $size[2];

$availableImageType = array('.webp'=>IMAGETYPE_WEBP, '.jpg'=>IMAGETYPE_JPEG, '.png'=>IMAGETYPE_PNG, '.gif'=>IMAGETYPE_GIF);

$db = RootDB::db();
$member = $db->queryFirstRow('SELECT `ID`, `PICTURE` FROM `member` WHERE `NO` = %i', $userID);


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
    $response['reason'] = 'webp, jpg, gif, png 파일이 아닙니다!';
    $response['result'] = false;
} elseif($image['size'] > 30720) {
    //파일크기 검사
    $response['reason'] = '30kb 이하로 올려주세요!';
    $response['result'] = false;
}  elseif($size[0] < 64 || 128 < $size[0]) {
    //이미지크기 검사
    $response['reason'] = '64x64 ~ 128x128 사이여야 합니다.';
    $response['result'] = false;
} elseif($size[0] != $size[1]) {
    //이미지크기 검사
    $response['reason'] = '1:1 비율 이미지여야 합니다.';
    $response['result'] = false;
}elseif($dt == $rf) {
    //갱신날짜 검사
    $response['reason'] = '1일 1회 변경 가능합니다!';
    $response['result'] = false;
} else {
    //이미지 저장

    while(true){
        $newPicName = bin2hex(random_bytes(4)).$newExt;
        $dest = AppConf::getUserIconPathFS().'/'.$newPicName;
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
        RootDB::db()->update('member',[
            'PICTURE' => $pic,
            'IMGSVR' => 1
        ], 'NO=%i', $userID);

        $servers = [];

        foreach(ServConfig::getServerList() as $key=>$setting){
            if($setting->isRunning()){
                $servers[] = [$key, $setting->getKorName()];
            }
        }

        $response['servers'] = $servers;

        $response['reason'] = '업로드에 성공했습니다!';
        $response['result'] = true;
    }
}


Json::die($response);