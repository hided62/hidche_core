<?php

class _String {
    public static function GetStrLen($str) {
        $count = strlen($str);
        $len = 0;
        for($i=0; $i < $count; ) {
            $code = ord($str[$i]);

            if($code >= 0xf0) {
                $len++;
                $i += 4;
            } elseif($code >= 0xe0) {
                $len++;
                $i += 3;
            } elseif($code >= 0xc2) {
                $len++;
                $i += 2;
            } else {
                $len++;
                $i += 1;
            }
        }
        return $len;
    }

    public static function SubStr($str, $s, $l=1000) {
        $count = strlen($str);
        $startByte = 0; $isSet = 0;
        $endByte = $count;
        $len = 0;
        for($i=0; $i < $count; ) {
            $code = ord($str[$i]);

            if($isSet == 0 && $len >= $s) {
                $startByte = $i; $isSet = 1;
            }
            if($isSet == 1 && $len-$s >= $l) {
                $endByte = $i;
                break;
            }

            if($code >= 0xf0) {
                $len++;
                $i += 4;
            } elseif($code >= 0xe0) {
                $len++;
                $i += 3;
            } elseif($code >= 0xc2) {
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

    public static function SubStrForWidth($str, $s, $w) {
        $count = strlen($str);
        $startByte = 0; $isSet = 0;
        $endByte = $count; $last = 0;
        $len = 0;
        $width = 0;
        for($i=0; $i < $count; ) {
            $code = ord($str[$i]);

            if($isSet == 0 && $len >= $s) {
                $startByte = $i; $isSet = 1;
                $width = 0;
            }
            if($isSet == 1 && $width == $w) {
                $endByte = $i;
                break;
            }
            if($isSet == 1 && $width > $w) {
                $endByte = $i - $last;
                break;
            }

            if($code >= 0xf0) {
                $len++;
                $width += 2;
                $last = 4;
                $i += 4;
            } elseif($code >= 0xe0) {
                $len++;
                $width += 2;
                $last = 3;
                $i += 3;
            } elseif($code >= 0xc2) {
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

    public static function CutStrForWidth($str, $s, $w, $ch='..') {
        $isCut = 0;
        $count = strlen($str);
        $startByte = 0; $isSet = 0;
        $endByte = $count; $last = 0;
        $len = 0;
        $width = 0;
        for($i=0; $i < $count; ) {
            $code = ord($str[$i]);

            if($isSet == 0 && $len >= $s) {
                $startByte = $i; $isSet = 1;
                $width = 0;
            }
            if($isSet == 1 && $width >= $w) {
                $endByte = $i - $last;
                $isCut = 1;
                break;
            }

            if($code >= 0xf0) {
                $len++;
                $width += 2;
                $last = 4;
                $i += 4;
            } elseif($code >= 0xe0) {
                $len++;
                $width += 2;
                $last = 3;
                $i += 3;
            } elseif($code >= 0xc2) {
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
        if($isCut != 0) {
            $str = substr($str, $startByte, $endByte-$startByte) . $ch;
        }
        return $str;
    }

    function Fill($str, $maxsize, $ch) {
        $size = strlen($str);

        $count = ($maxsize - $size) / 2;

        for($i=0; $i < $count; $i++) {
            $string = $string.$ch;
        }
        $string = $string.$str;
        for($i=0; $i < $count; $i++) {
            $string = $string.$ch;
        }
        return $string;
    }

    function Fill2($str, $maxsize, $ch='0') {
        $size = strlen($str);

        $count = ($maxsize - $size);

        for($i=0; $i < $count; $i++) {
            $string = $string.$ch;
        }
        $string = $string.$str;

        return $string;
    }

    public static function EscapeTag($str) {
        $str = htmlspecialchars($str);
        $str = str_replace("\r\n", "<br>", $str);
        $str = str_replace("\n", "<br>", $str);
//        return nl2br(htmlspecialchars($str));
//        return htmlspecialchars($str);
        return $str;
    }

    public static function NoSpecialCharacter($str) {
        $str = str_replace(" ", "", $str);
        $str = str_replace("\"", "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace("ⓝ", "", $str);
        $str = str_replace("ⓜ", "", $str);
        $str = str_replace("ⓖ", "", $str);
        $str = str_replace("\\", "", $str);
        $str = str_replace("/", "", $str);
        $str = str_replace("`", "", $str);
        $str = str_replace("-", "", $str);
        $str = str_replace("=", "", $str);
        $str = str_replace("[", "", $str);
        $str = str_replace("]", "", $str);
        $str = str_replace(";", "", $str);
        $str = str_replace(",", "", $str);
        $str = str_replace(".", "", $str);
        $str = str_replace("~", "", $str);
        $str = str_replace("!", "", $str);
        $str = str_replace("@", "", $str);
        $str = str_replace("#", "", $str);
        $str = str_replace("$", "", $str);
        $str = str_replace("%", "", $str);
        $str = str_replace("^", "", $str);
        $str = str_replace("&", "", $str);
        $str = str_replace("*", "", $str);
        $str = str_replace("(", "", $str);
        $str = str_replace(")", "", $str);
        $str = str_replace("_", "", $str);
        $str = str_replace("+", "", $str);
        $str = str_replace("|", "", $str);
        $str = str_replace("{", "", $str);
        $str = str_replace("}", "", $str);
        $str = str_replace(":", "", $str);
        $str = str_replace("", "", $str);
        $str = str_replace("<", "", $str);
        $str = str_replace(">", "", $str);
        $str = str_replace("?", "", $str);
        $str = str_replace("　", "", $str);
        return $str;
    }
}

?>
