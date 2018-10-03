<?php
namespace sammo;

class Json
{
    const PRETTY = 1 << 0;
    const DELETE_NULL = 1 << 1;
    const NO_CACHE = 1 << 2;
    const PASS_THROUGH = 1 << 3;
    const EMPTY_ARRAY_IS_DICT = 1 << 4;

    public static function encode($value, $flag = 0)
    {
        $rawFlag = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($flag & static::PRETTY) {
            $rawFlag |= JSON_PRETTY_PRINT;
        }
        if ($flag & static::DELETE_NULL) {
            $value = Util::eraseNullValue($value);
        }

        if($value === [] && ($flag & static::EMPTY_ARRAY_IS_DICT)){
            $value = (object)null;
        }
        return json_encode($value, $rawFlag);
    }

    public static function decode($value)
    {
        if($value === null){
            return null;
        }
        return json_decode($value, true);
    }

    public static function decodeObj($value){
        //NOTE: 구 코드가 모두 '배열'을 가정하기 때문에 decode는 연관배열로 반환하였으나, 
        //호환을 위해서는object로 반환하는 것이 더 나을것
        return json_decode($value);
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
