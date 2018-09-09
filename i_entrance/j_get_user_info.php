<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

// 외부 파라미터

$db = RootDB::db();
$member = $db->queryFirstRow('SELECT `id`, `name`, `grade`, `picture`, reg_date, third_use, acl, oauth_type, token_valid_until FROM `member` WHERE `NO` = %i', $userID);

if(!$member['picture']){
    $picture = ServConfig::getSharedIconPath().'/default.jpg';
}
else{
    $picture = $member['picture'];
    if(strlen($picture) > 11){
        $picture = substr($picture, 0, -10);
    }

    $pictureFSPath = AppConf::getUserIconPathFS().'/'.$picture;

    if(file_exists($pictureFSPath)){
        $picture = AppConf::getUserIconPathWeb().'/'.$picture;
    }
    else{
        $picture = ServConfig::getSharedIconPath().'/'.$picture;
    }
}

$tokenValidUntil = $member['token_valid_until'];

if($member['grade'] == 6) {
    $grade = '운영자';
} elseif($member['grade'] == 5) {
    $grade = '부운영자';
} elseif($member['grade'] > 1) {
    $grade = '특별회원';
} elseif($member['grade'] == 1) {
    $grade = '일반회원';
} elseif($member['grade'] == 0) {
    $grade = '블럭회원';
}

$acl = [];
//TODO: Acl을 관리하기 위한 별도의 객체 필요
foreach(Json::decode($member['acl']??'{}') as $serverName=>$aclList){
    $serverKorName = AppConf::getList()[$serverName]->getKorName();
    $aclTextList = array_map(function($aclName){
        $aclText = "알수없음[{$aclName}]";
        switch($aclName){
            case 'openClose':$aclText='서버여닫기';break;
            case 'reset':$aclText='서버리셋';break;
            case 'update':$aclText='업데이트';break;
            case 'fullUpdate':$aclText='임의업데이트';break;
            case 'vote':$aclText='설문조사';break;
            case 'globalNotice':$aclText='전역공지';break;
            case 'notice':$aclText='공지';break;
            case 'blockGeneral':$aclText='장수징계';break;
        }
        return $aclText;
    }, $aclList);

    $acl[] = sprintf("%s(%s)", $serverKorName, join(",", $aclTextList));
}

if($acl){
    $acl = join(",<br>", $acl);
}
else{
    $acl = "-";
}

Json::die([
    'result'=>true,
    'reason'=>'success',
    'id'=>$member['id'],
    'name'=>$member['name'],
    'grade'=>$grade,
    'picture'=>$picture,
    'global_salt'=>RootDB::getGlobalSalt(),
    'join_date'=>$member['reg_date'],
    'third_use'=>($member['third_use']!=0),
    'acl'=>$acl,
    'oauth_type'=>$member['oauth_type'],
    'token_valid_until'=>$tokenValidUntil

]);
