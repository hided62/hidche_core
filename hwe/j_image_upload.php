<?php
namespace sammo;

include 'lib.php';
include 'func.php';

$session = Session::requireGameLogin([])->setReadOnly();
$userID = $session::getUserID();
$serverID = UniqueConst::$serverID;

$image = $_FILES['img'];
switch ($image['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_NO_FILE:
        Json::die([
            'result'=>false,
            'reason'=>'파일이 없습니다.'
        ]);
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        Json::die([
            'result'=>false,
            'reason'=>'업로드된 파일이 지나치게 큽니다.'
        ]);
    default:
        Json::die([
            'result'=>false,
            'reason'=>'업로드되지 않았습니다.'
        ]);
}

if(!is_uploaded_file($image['tmp_name'])) {
    Json::die([
        'result'=>false,
        'reason'=>'제대로 파일이 업로드되지 않았습니다.'
    ]);
}

if($image['size'] > 1048576) {
    //파일크기 검사
    Json::die([
        'result'=>false,
        'reason'=>'1MB 이하로 올려주세요!'
    ]);
}

$size = getImageSize($image['tmp_name']);

$imageType = $size[2];
$availableImageType = array('.jpg'=>IMAGETYPE_JPEG, '.png'=>IMAGETYPE_PNG, '.gif'=>IMAGETYPE_GIF);
$newExt = array_search($imageType, $availableImageType, true);

if(!$newExt) {
    Json::die([
        'result'=>false,
        'reason'=>'jpg, gif, png 파일이 아닙니다!'
    ]);
}

$db = RootDB::db();
$imgStor = KVStorage::getStorage($db, 'img_storage');

$picName = hash_file('md5', $image['tmp_name']);
$newPicName = "$picName$newExt";

$destDir = AppConf::getUserIconPathFS().'/uploaded_image';
$dest = $destDir.'/'.$newPicName;

if(!file_exists($dest)){
    if (!file_exists($destDir)) {
        mkdir($destDir);
    }
    if(!is_dir($destDir)) {
        Json::die([
            'result'=>false,
            'reason'=>'버그! 업로드 경로 확인!'
        ]);
    }
    if(!is_writable($destDir)){
        Json::die([
            'result'=>false,
            'reason'=>'버그! 업로드 권한 확인!'
        ]);
    }
    
    $dest = $destDir.'/'.$newPicName;
    
    if(!move_uploaded_file($image['tmp_name'], $dest)) {
        Json::die([
            'result'=>false,
            'reason'=>'업로드에 실패했습니다!'
        ]);
    }
}

$storedStatus = $imgStor->$newPicName??[];
$imgKey = "$serverID:$userID";
if(!key_exists($imgKey, $storedStatus)){
    $storedStatus[$imgKey] = TimeUtil::now();
}

$imgStor->$newPicName = $storedStatus;

Json::die([
    'result'=>true,
    'reason'=>'성공',
    'path'=>AppConf::getUserIconPathWeb().'/uploaded_image/'.$newPicName
]);