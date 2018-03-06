<?php
require(__dir__.'/../vendor/autoload.php');
require(__dir__.'/../d_setting/conf.php');
use utilphp\util as util;

/**
 * conf.php 파일 생성용 코드
 * 
 * 이 파일만 예외적으로 lib.php, func.php를 참조하지 않고 독자적으로 동작함.
 */

function parseJsonPost(){
    // http://thisinterestsme.com/receiving-json-post-data-via-php/
    // http://thisinterestsme.com/php-json-error-handling/
    if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
        throw new Exception('Request method must be POST!');
    }
    
    //Make sure that the content type of the POST request has been set to application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if(strcasecmp($contentType, 'application/json') != 0){
        throw new Exception('Content type must be: application/json');
    }
    
    //Receive the RAW post data.
    $content = trim(file_get_contents("php://input"));
    
    //Attempt to decode the incoming RAW post data from JSON.
    $decoded = json_decode($content, true);
    
    
    $jsonError = json_last_error();
    
    //In some cases, this will happen.
    if(is_null($decoded) && $jsonError == JSON_ERROR_NONE){
        throw new Exception('Could not decode JSON!');
    }
    
    //If an error exists.
    if($jsonError != JSON_ERROR_NONE){
        $error = 'Could not decode JSON! ';
        
        //Use a switch statement to figure out the exact error.
        switch($jsonError){
            case JSON_ERROR_DEPTH:
                $error .= 'Maximum depth exceeded!';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $error .= 'Underflow or the modes mismatch!';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $error .= 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $error .= 'Malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                 $error .= 'Malformed UTF-8 characters found!';
            break;
            default:
                $error .= 'Unknown error!';
            break;
        }
        throw new Exception($error);
    }

    return $decoded;
}

function returnJson($value, $noCache = true, $pretty = false, $die = true){
    header('Content-Type: application/json');

    if($noCache){
        header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
    }

    if($pretty){
        $flag = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
    }
    else{
        $flag = JSON_UNESCAPED_UNICODE;
    }
    echo json_encode($value, $flag); 
    if($die){
        die();
    }
}

if(file_exist(__dir__.'/d_setting/conf.php')){
    returnJson([
        'result'=>false,
        'reason'=>'이미 설정 파일이 존재합니다.'
    ]);
}

