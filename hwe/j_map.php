<?php
namespace sammo;

include 'lib.php';
include 'func.php';

$session = Session::getInstance()->setReadOnly();

$defaultPost = [
    'year' => null,
    'month' => null,
    'aux' => null,
    'neutralView' => false,
    'showMe' => true
];
$post = WebUtil::parseJsonPost() + $defaultPost;

if(!$session->isLoggedIn() || !$session->generalID){
    $post['neutralView'] = true;
    $post['showMe'] = false;
}


if($post['year']){
    if($post['month'] < 1 || $post['month'] > 12){
        Json::die([
            'result'=>false,
            'reason'=>'잘못된 개월 값'
        ]);
    }

    $post['year'] = Util::toInt($post['year']);
    $post['month'] = Util::toInt($post['month']);
}
else{
    $post['year'] = null;
    $post['month'] = null;
}

$result = getWorldMap($post);
if($post['year'] && $post['month'] && $result['result']){
    //연감 자료는 캐싱 가능
    Json::die($result, false);
}
else{
    Json::die($result);
}
