<?php
namespace sammo;

class Json
{
    const PRETTY = 1;
    const DELETE_NULL = 2;
    const NO_CACHE = 4;
    const PASS_THROUGH = 8;

    public static function encode($value, $flag = 0)
    {
        $rawFlag = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($flag & static::PRETTY) {
            $rawFlag |= JSON_PRETTY_PRINT;
        }
        if ($flag & static::DELETE_NULL) {
            $value = Util::eraseNullValue($value);
        }
        return json_encode($value, $rawFlag);
    }

    public static function decode($value)
    {
        return json_decode($value, true);
    }

    public static function die($value, $flag = self::NO_CACHE)
    {
        //NOTE: REST 형식에 맞게, ok(), fail()로 쪼개는게 낫지 않을까 생각해봄.
        if ($flag & static::NO_CACHE) {
            WebUtil::setHeaderNoCache();
        }
        
        header('Content-Type: application/json');
    
        if($flag & static::PASS_THROUGH){
            die($value);
        }
        die(Json::encode($value, $flag));
    }
}
