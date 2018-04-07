<?php
namespace sammo;

class StringUtil
{
    public static function GetStrLen($str)
    {
        $count = strlen($str);
        $len = 0;
        for ($i=0; $i < $count;) {
            $code = ord($str[$i]);

            if ($code >= 0xf0) {
                $len++;
                $i += 4;
            } elseif ($code >= 0xe0) {
                $len++;
                $i += 3;
            } elseif ($code >= 0xc2) {
                $len++;
                $i += 2;
            } else {
                $len++;
                $i += 1;
            }
        }
        return $len;
    }

    public static function SubStr($str, $s, $l=1000)
    {
        $count = strlen($str);
        $startByte = 0;
        $isSet = 0;
        $endByte = $count;
        $len = 0;
        for ($i=0; $i < $count;) {
            $code = ord($str[$i]);

            if ($isSet == 0 && $len >= $s) {
                $startByte = $i;
                $isSet = 1;
            }
            if ($isSet == 1 && $len-$s >= $l) {
                $endByte = $i;
                break;
            }

            if ($code >= 0xf0) {
                $len++;
                $i += 4;
            } elseif ($code >= 0xe0) {
                $len++;
                $i += 3;
            } elseif ($code >= 0xc2) {
                $len++;
                $i += 2;
            } else {
                $len++;
                $i += 1;
            }
        }
        $str = substr($str, $startByte, $endByte-$startByte);
        return $str;
    }

    public static function SubStrForWidth($str, $s, $w)
    {
        $count = strlen($str);
        $startByte = 0;
        $isSet = 0;
        $endByte = $count;
        $last = 0;
        $len = 0;
        $width = 0;
        for ($i=0; $i < $count;) {
            $code = ord($str[$i]);

            if ($isSet == 0 && $len >= $s) {
                $startByte = $i;
                $isSet = 1;
                $width = 0;
            }
            if ($isSet == 1 && $width == $w) {
                $endByte = $i;
                break;
            }
            if ($isSet == 1 && $width > $w) {
                $endByte = $i - $last;
                break;
            }

            if ($code >= 0xf0) {
                $len++;
                $width += 2;
                $last = 4;
                $i += 4;
            } elseif ($code >= 0xe0) {
                $len++;
                $width += 2;
                $last = 3;
                $i += 3;
            } elseif ($code >= 0xc2) {
                $len++;
                $width += 2;
                $last = 2;
                $i += 2;
            } else {
                $len++;
                $width += 1;
                $last = 1;
                $i += 1;
            }
        }
        $str = substr($str, $startByte, $endByte-$startByte);
        return $str;
    }

    public static function CutStrForWidth($str, $s, $w, $ch='..')
    {
        $isCut = 0;
        $count = strlen($str);
        $startByte = 0;
        $isSet = 0;
        $endByte = $count;
        $last = 0;
        $len = 0;
        $width = 0;
        for ($i=0; $i < $count;) {
            $code = ord($str[$i]);

            if ($isSet == 0 && $len >= $s) {
                $startByte = $i;
                $isSet = 1;
                $width = 0;
            }
            if ($isSet == 1 && $width >= $w) {
                $endByte = $i - $last;
                $isCut = 1;
                break;
            }

            if ($code >= 0xf0) {
                $len++;
                $width += 2;
                $last = 4;
                $i += 4;
            } elseif ($code >= 0xe0) {
                $len++;
                $width += 2;
                $last = 3;
                $i += 3;
            } elseif ($code >= 0xc2) {
                $len++;
                $width += 2;
                $last = 2;
                $i += 2;
            } else {
                $len++;
                $width += 1;
                $last = 1;
                $i += 1;
            }
        }
        if ($isCut != 0) {
            $str = substr($str, $startByte, $endByte-$startByte) . $ch;
        }
        return $str;
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

    public static function EscapeTag($str)
    {
        $str = htmlspecialchars($str);
        $str = str_replace(["\r\n", "\r", "\n"], '<br>', $str);
//        return nl2br(htmlspecialchars($str));
//        return htmlspecialchars($str);
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
