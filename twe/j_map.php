<?php
namespace sammo;

include 'lib.php';
include 'func.php';



getGeneralID();

//읽기 전용이다. 빠르게 세션 끝내자
session_write_close();

$defaultPost = [
    'year' => null,
    'month' => null,
    'aux' => null,
    'neutralView' => false,
    'showMe' => true
];
$post = array_merge($defaultPost, WebUtil::parseJsonPost());


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
