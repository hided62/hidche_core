<?php
include 'lib.php';
include 'func.php';

use utilphp\util as util;

//읽기 전용이다. 빠르게 세션 끝내자
session_write_close();

$defaultPost = [
    'year' => null,
    'month' => null,
    'aux' => null,
    'neutralView' => false,
    'showMe' => true
];
$post = array_merge($defaultPost, parseJsonPost());


if($post['year']){
    if($post['month'] < 1 || $post['month'] > 12){
        returnJson([
            'result'=>false,
            'reason'=>'잘못된 개월 값'
        ]);
    }

    $post['year'] = intval($post['year']);
    $post['month'] = intval($post['month']);
}
else{
    $post['year'] = null;
    $post['month'] = null;
}

returnJson(getWorldMap($post));