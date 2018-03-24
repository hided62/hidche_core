<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');
require(__dir__.'/../d_setting/conf.php');


/**
 * conf.php 파일 생성용 코드
 * 
 * 이 파일만 예외적으로 lib.php, func.php를 참조하지 않고 독자적으로 동작함.
 */

if(file_exist(__dir__.'/d_setting/conf.php')){
    Json::die([
        'result'=>false,
        'reason'=>'이미 설정 파일이 존재합니다.'
    ]);
}

