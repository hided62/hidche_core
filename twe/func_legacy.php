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
// (자신의 턴이 되면 다시 접속 가능합니다. <font color=orange size=4>제한량을 늘리기 위해 참여해주세요!</font> <font color=magenta size=4>참여게시판 참고.</font>)<br>
