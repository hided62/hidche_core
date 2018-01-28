<?php

function CheckLogin($type=0) {
    if(!isset($_SESSION['p_id'])) {
        if($type == 0) {
            header('Location: start.php');
             //echo "<script>location.replace('start.php');</script>"; 
             //echo 'start.php';//TODO:debug all and replace
            }
        else           { 
            header('Location: main.php');
            //echo 'main.php';//TODO:debug all and replace
            //echo "<script>window.top.main.location.replace('main.php');</script>";
         }
        exit();
    }
}


function printLimitMsg($turntime) {
    echo "
<html>
<head>
<title>접속제한</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
";
require('analytics.php');
echo "
</head>
<body oncontextmenu='return false'>
<font size=4><b>
접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다. (다음 접속 가능 시각 : {$turntime})<br>
(자신의 턴이 되면 다시 접속 가능합니다. 당신의 건강을 위해 잠시 쉬어보시는 것은 어떨까요? ^^)<br>
</b></font>
</body>
</html>
";
}


function bar($per, $skin=1, $h=7) {
    global $images;
    if($h == 7) { $bd = 0; $h =  7; $h2 =  5; }
    else        { $bd = 1; $h = 12; $h2 =  8; }

    $per = round($per, 1);
    if($per < 1 || $per > 99) { $per = round($per); }
    $str1 = "<td width={$per}% background={$images}/pb{$h2}.gif>&nbsp;</td>";
    $str2 = "<td width=*% background={$images}/pr{$h2}.gif>&nbsp;</td>";
    if($per <= 0) { $str1 = ""; }
    elseif($per >= 100) { $str2 = ""; }
    if($skin == 0) {
        $str = "-";
    } else {
        $str = "
        <table width=100% height={$h} border={$bd} cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:1;>
            <tr>{$str1}{$str2}</tr>
        </table>";
    }
    return $str;
}
