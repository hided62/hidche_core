<?php
namespace sammo;

class Util extends \utilphp\util{
    public static function hashPassword($salt, $password){
        return hash('sha512', $salt.$password.$salt);
    }

    /**
     * 변환할 내용이 _tK_$key_ 형태로 작성된 단순한 템플릿 파일을 이용하여 결과물을 생성해주는 함수.
     */
    public static function generateFileUsingSimpleTemplate(string $srcFilePath, string $destFilePath, array $params, bool $canOverwrite=false){
        if($destFilePath === $srcFilePath){
            return 'invalid destFilePath';
        }
        if(!file_exists($srcFilePath)){
            return 'srcFilePath is not exists';
        }
        if(file_exists($destFilePath) && !$canOverwrite){
            return 'destFilePath is already exists';
        }
        if(!is_writable(dirname($destFilePath))){
            return 'destFilePath is not writable';
        }

        $text = file_get_contents($srcFilePath);
        foreach($params as $key => $value){
            $text = str_replace("_tK_{$key}_", $value, $text);
        }
        file_put_contents($destFilePath, $text);

        return true;
    }

    /**
     * '비교적' 안전한 int 변환
     * null -> null
     * int -> int
     * float -> int
     * numeric(int, float) 포함 -> int
     * 기타 -> 예외처리
     * 
     * @return int|null
     */
    public static function toInt($val, $force=false){
        if($val === null){
            return null;
        }
        if(is_int($val)){
            return $val;
        }
        if(is_numeric($val)){
            return intval($val);//
        }
        if(strtolower($val) === 'null'){
            return null;
        }

        if($force){
            return intval($val);
        }
        throw new InvalidArgumentException('올바르지 않은 타입형 :'.$val);
    }

    /**
     * Generate a random string, using a cryptographically secure 
     * pseudorandom number generator (random_int)
     * 
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     * 
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    public static function randomStr($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function mapWithDict($callback, $dict){
        $result = [];
        foreach(array_keys($dict) as $key){
            $result[$key] = ($callback)($dict[$key]);
        }
        return $result;
    }

    public static function convertArrayToDict($arr, $keyName){
        $result = [];
    
        foreach($arr as $obj){
            $key = $obj[$keyName];
            $result[$key] = $obj;
        }
    
        return $result;
    }

    public static function convertDictToArray($dict, $keys){
        $result = [];
    
        foreach($keys as $key){
            $result[] = Util::array_get($dict[$key], null);
        }
        return $result;
    }

    public static function isDict(&$array){
        if(!is_array($array)){
            //배열이 아니면 dictionary 조차 아님.
            return false;
        }
        $idx = 0;
        $jmp = 0;
        foreach ($arr as $key=>&$value) {
            if(is_string($key)){
                return true;
            }
            $jmp = $key - $idx - 1;
            $idx = $key;
        }
    
        if ($jmp * 5 >= count($array)){
            //빈칸이 많으면 dictionary인걸로.
            return true;
        }
        else{
            return false;
        }
    }

    public static function eraseNullValue($dict, $depth=512){
        //TODO:Test 추가
        if($dict === null){
            return null;
        }
    
        if(is_array($dict) && empty($dict)){
            return null;
        }
    
        if($depth <= 0){
            return $dict;
        }
    
        foreach ($arr as $key=>$value) {
            if($value === null){
                unset($dict[$key]);
            }
            else if(Util::isDict($value)){
                $newValue = Util::eraseNullValue($value, $depth - 1);
                if($newValue === null){
                    unset($dict[$key]);
                }
                else{
                    $dict[$key] = $newValue;
                }
                
            }
        }
    
        return $dict;
    }

    

};