<?php
namespace sammo;

class Json{
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
            WebUtil::setHeaderNoCache();
        }
        
        header('Content-Type: application/json');
    
        echo Json::encode($value, $pretty); 
        if($die){
            die();
        }
    }
}