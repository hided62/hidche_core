<?php
namespace sammo;


function getFont(string $str) {
    if(strlen($str) >= 22) { $str = "<font size=1>{$str}</font>"; }

    return $str;
}

function SQ2DQ(string $str) {
    return str_replace("'", "&#039;", $str);
}

function Tag2Code(string $str) {
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

function BadTag2Code(string $str) {
    /* FIXME: 제대로된 tag 변환 코드 사용 */
    $str = str_replace("<script", "<sorry", $str);
    $str = str_replace("</script", "</sorry", $str);
    return $str;
}

function tab(string $str, $maxsize, $ch) {
    $size = strlen($str);

    $string = '';
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

function tab2(string $str, $maxsize, $ch) {
    $size = strlen($str);

    $string = '';
    $count = ($maxsize - $size);

    for($i=0; $i < $count; $i++) {
        $string = "$string" . $ch;
    }
    $string = "$string" . "$str";

    return $string;
}



