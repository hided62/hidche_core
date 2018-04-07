<?php
namespace sammo;

class StringUtil
{
    /**
     * 전각, 반각 길이 기준의 substr
     * @param string $str 원본 문자열
     * @param int $start 시작 너비. 일치하는 문자가 없을 경우 그 다음 문자부터. 음수의 경우 뒤에서부터.
     * @param int|null $width 길이. null일 경우 끝까지.
     */
    public static function subStringForWidth(string $str, int $start = 0, $width = null)
    {
        $length = mb_strwidth($str, 'UTF-8');
        if($width === null){
            $width = $length;
        }

        if($start < 0){
            $start = $length - $start;
        }

        /* 길이를 직관적으로 알아낼 수 있는 방법은 '없다'.
         * utf8의 경우 뒤에서부터 세는 것 전략이 가능하나 제외함. 
         */

        $currentPos = 0;
        $currentWidth = 0;

        $strings = static::splitString($str);
        foreach($strings as $idx=>$char){
            $charWidth = mb_strwidth($char, 'UTF-8');
            if($currentPos+$charWidth > $start){
                break;
            }
            $currentPos += $charWidth;
        }

        if($currentPos + $width >= $length){
            return substr($str, $currentPos, null);
        }

        for($idx = $currentPos; $idx < count($strings); $idx++){
            $char = $strings[$idx];

            $charWidth = mb_strwidth($char, 'UTF-8');
            if($currentWidth + $charWidth > $width){
                break;
            }
            $currentWidth += $charWidth;
        }

        return substr($str, $currentPos, $currentWidth);
    }

    public static function cutStringForWidth(string $str, int $width, string $endFill='..')
    {
        if(mb_strwidth($str) <= $width){
            return $str;
        }

        $result = '';
        $width -= mb_strwidth($endFill, 'UTF-8');

        foreach(static::splitString($str) as $char){
            $charWidth = mb_strwidth($char, 'UTF-8');
            if($charWidth > $width){
                break;
            }
            $result .= $char;
            $width -= $charWidth;
        }

        return $result.$endFill;
    }

    function splitString($str, $l = 0) {
        //https://php.net/manual/kr/function.str-split.php#107658
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * str_pad를 유니코드에서 사용할 수 있는 함수, monospace 전각, 반각 구분을 포함.
     * @param string $str   원본 문자열
     * @param int $maxsize  채우고자 하는 너비. 전각 문자는 2, 반각 문자는 1을 기준으로 함
     * @param string $ch    채움 문자열
     * @param int $position 원본 문자열의 위치. -1:왼쪽(오른쪽을 채움), 0:가운데(양쪽을 채움), 1:오른쪽(왼쪽을 채움)
     * @return string 채워진 문자열. 완벽히 채울 수 없는 경우 $maxsize보다 살짝 작은 길이로 반환 됨.
     */
    public static function padString(string $str, int $maxsize, string $ch = ' ', int $position = 0){
        $chLen = mb_strwidth($ch, 'UTF-8');

        if($chLen == 0){
            return padString($str, $maxsize, ' ', $position);
        }

        $textLen = mb_strwidth($str, 'UTF-8');

        $fillTextCnt = intdiv($maxsize - $textLen, $chLen);

        if($position < 0){
            $fillLeftCnt = 0;
            $fillRightCnt = $fillTextCnt;
        }
        else if($position > 0){
            $fillLeftCnt = $fillTextCnt;
            $fillRightCnt = 0;
        }
        else {
            $fillLeftCnt = intdiv($fillTextCnt, 2);
            $fillRightCnt = $fillTextCnt - $fillLeftCnt;
        }

        return str_repeat($ch, $fillLeftCnt).$str.str_repeat($ch, $fillRightCnt);
    }

    public static function padStringAlignRight(string $str, int $maxsize, string $ch = ' '){
        return static::padString($str, $maxsize, $ch, 1);
    }

    public static function padStringAlignLeft(string $str, int $maxsize, string $ch = ' '){
        return static::padString($str, $maxsize, $ch, -1);
    }

    public static function padStringAlignCenter(string $str, int $maxsize, string $ch = ' '){
        return static::padString($str, $maxsize, $ch, 0);
    }

    public static function escapeTag($str)
    {
        $str = htmlspecialchars($str);
        $str = str_replace(["\r\n", "\r", "\n"], '<br>', $str);
        return $str;
    }

    public static function removeSpecialCharacter($str)
    {
        return str_replace([
            ' ', '"', '\'', 'ⓝ', 'ⓜ', 'ⓖ', '\\', '/', '`',
            '-', '=', '[', ']', ';', ',', '.', '~', '!', '@',
            '#', '$', '%', '^', '&', '*', '(', ')', '_', '+',
            '|', '{', '}', ':', '', '<', '>', '?', '　'
        ], '', $str);
    }
}
