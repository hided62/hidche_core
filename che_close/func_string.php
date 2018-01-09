<?

function getFont($str) {
    if(strlen($str) >= 22) { $str = "<font size=1>{$str}</font>"; }

    return $str;
}

function unfont($str) {
    $str = str_replace("<font color=cyan>", "", $str);
    $str = str_replace("<font color=limegreen>", "", $str);
    $str = str_replace("<font color=magenta>", "", $str);
    $str = str_replace("<font color=red>", "", $str);
    $str = str_replace("</font>", "", $str);
    return $str;
}

function SQ2DQ($str) {
    return str_replace("'", "&#039;", $str);
}

function Tag2Code($str) {
//    return htmlspecialchars(nl2br(str_replace(" ", "&nbsp;", $str)));
//    $str = str_replace("&", "&amp;", $str);
    $str = str_replace("'", "&#039;", $str);
    $str = str_replace("\"", "&quot;", $str);
    $str = str_replace("<", "&lt", $str);
    $str = str_replace(">", "&gt", $str);
//    $str = str_replace(" ", "&nbsp;", $str);
//    return htmlspecialchars(nl2br($str));
    return nl2br($str);
}

function BadTag2Code($str) {
    $str = str_replace("<script", "<sorry", $str);
    $str = str_replace("<embed", "<sorry", $str);
    return $str;
}

function tab($str, $maxsize, $ch) {
    $size = strlen($str);

    $count = ($maxsize - $size) / 2;

    for($i=0; $i < $count; $i++) {
        $string = "$string" . $ch;
    }
    $string = "$string" . "$str";
    for($i=0; $i < $count; $i++) {
        $string = "$string" . $ch;
    }
    return $string;
}

function tab2($str, $maxsize, $ch) {
    $size = strlen($str);

    $count = ($maxsize - $size);

    for($i=0; $i < $count; $i++) {
        $string = "$string" . $ch;
    }
    $string = "$string" . "$str";

    return $string;
}

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

    //중간정렬
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

    //우측정렬
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
