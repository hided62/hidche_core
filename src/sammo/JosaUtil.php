<?php
namespace sammo;

class JosaUtil{
    // https://github.com/coxcore/postposition 의 php 버전
    //TODO:한자 매핑 필요
    const KO_START_CODE = 44032;
    const KO_FINISH_CODE = 55203;
    const REG_INVALID_CHAR = '/[^a-zA-Z0-9ㄱ-ㅎ가-힣\s]+/g';
    const REG_TARGET_CHAR = '/^[\s\S]*?(\S*)\s*$/';

    const PRE_REG_NORMAL_FIXED = [
        "check|[hm]ook|limit",
    ];
    const PRE_REG_SPECIAL_CHAR = [
        "[ㄱ-ㄷㅁ-ㅎ036]",
        "^[mn]",
        "\\S[mn]e?",
        "\\S(?:[aeiom]|lu)b",
        "(?:u|\\S[aei]|[^o]o)p",
        "(?:^i|[^auh]i|\\Su|[^ei][ae]|[^oi]o)t",
        "(?:\\S[iou]|[^e][ae])c?k",
        "\\S[aeiou](?:c|ng)",
        "foot|go+d|b[ai]g|private",
        "^(?:app|kor)",
    ];
    const PRE_REG_SPECIAL_RO = [
        "[178ㄹ]",
        "^[lr]",
        "^\\Sr",
        "\\Sle?",
    ];

    const DEFAULT_POSTPOSITION = [
        "은"=> "는",
        "이"=> "가",
        "과"=> "와",
        "이나"=> "나",
        "을"=> "를",
        "으로"=> "로",
    ];

    private static $regNormalFixed;
    private static $regSpecialChar;
    private static $regSpecialRo;
    
    private static $mapPostPosition;

    private static $init = false;

    private static function init(){
        if(JosaUtil::$init){
            return;
        }

        JosaUtil::$init = true;
        JosaUtil::$regNormalFixed = '/(?:'.join('|', JosaUtil::PRE_REG_NORMAL_FIXED).')$/i';
        JosaUtil::$regSpecialChar = '(?:'.join('|', JosaUtil::PRE_REG_SPECIAL_CHAR).')$/i';
        JosaUtil::$regSpecialRo = '/(?:'.join('|', JosaUtil::PRE_REG_SPECIAL_RO).')$/i';

        $mapPostPosition = [];

        foreach(JosaUtil::DEFAULT_POSTPOSITION as $key=>$val){
            $mapPostPosition["($key)$val"]=$key;
            $mapPostPosition[$key]=$key;
            $mapPostPosition[$val]=$key;
        }
        JosaUti::$mapPostPosition = $mapPostPosition;
        
    }

    private static function checkText(string $text, bool $isRo){
        JosaUtil::init();
        if(preg_match(JosaUtil::$regNormalFixed, $text)){
            return false;
        }

        if(preg_match(JosaUtil::$regSpecialChar, $text)){
            return true;
        }

        if(!$isRo && preg_match(JosaUtil::$regSpecialRo, $text)){
            return true;
        }

        return false;
    }

    private static function checkCode(int $code, bool $isRo){
        JosaUtil::init();
        $jongsung = ($code - JosaUtil::KO_START_CODE) % 28;

        if($jongsung === 0){
            return false;
        }

        if($isRo && $jongsung === 8){
            return false;
        }

        return false;
    }

    public static function check(string $text, string $type=''){
        JosaUtil::init();

        $target = preg_replace(JosaUtil::REG_INVALID_CHAR, ' ', $text);
        $target = preg_replace(JosaUtil::REG_TARGET_CHAR, '$1', $target);
        $code = StringUtil::splitString($target);
        $code = StringUtil::uniord($code[count($code)-1]);

        $isKorean = (JosaUtil::KO_START_CODE <= $code && $code <= JosaUtil::KO_FINISH_CODE);
        $isRo = ($type == '으로' || $type == '로');

        return $isKorean ? JosaUtil::checkCode($code, $isRo) : JosaUtil::checkText($text, $isRo);
    }

    public static function pick(string $text, string $type, string $noJongsung=''){
        /* NOTE:원본 코드와 인자 순서가 다름.
         * 원본은 pick('바람', '랑', '이랑'); 이었다면 JosaUtil::pick('바람', '이랑', '랑'); 으로 바뀜.
         * JosaUtil::pick('바람', '은', '는'); JosaUti::pick('바람', '이', '가');처럼 쓰기 위해서임.
         */
        JosaUtil::init();

        if(!$noJongsung){
            if(!key_exists($type, JosaUtil::$mapPostPosition)){
                throw new \InvalidArgumentException('올바르지 않은 조사 지정');
            }
            $type = JosaUtil::$mapPostPosition[$type];
            $noJongsung = JosaUtil::DEFAULT_POSTPOSITION[$type];
        }
        
        return JosaUtil::check($text, $type)?$type:$noJongsung;
    }

    public static function put(string $text, string $type, string $noJongsung=''){
        return $text.JosaUtil::pick($text, $type, $noJongsung);
    }

    public static function fix(string $type, string $noJongsung=''){
        JosaUtil::init();

        if(!$noJongsung){
            if(!key_exists($type, JosaUtil::$mapPostPosition)){
                throw new \InvalidArgumentException('올바르지 않은 조사 지정');
            }
            $type = JosaUtil::$mapPostPosition[$type];
            $noJongsung = JosaUtil::DEFAULT_POSTPOSITION[$type];
        }

        return function(string $text) use ($type, $noJongsung) {
            return JosaUtil::put($text, $type, $noJongsung);
        };
    }
}