<?php
namespace sammo;

class Json{
    private static function setHeaderNoCache(){
        if(!headers_sent()) {
            header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', FALSE);
            header('Pragma: no-cache');
        }
    }

    public static function encode($value, $pretty = false){
        if($pretty){
            $flag = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        }
        else{
            $flag = JSON_UNESCAPED_UNICODE;
        }
        return json_encode($value, $flag); 
    }

    public static function decode($value){
        return json_decode($value, true);
    }

    public static function die($value, $noCache = true, $pretty = false, $die = true){
        if($noCache){
            self::setHeaderNoCache();
        }
        
        header('Content-Type: application/json');
    
        echo Json::encode($value, $pretty); 
        if($die){
            die();
        }
    }
}