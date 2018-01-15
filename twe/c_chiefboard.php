<?php
// $title, $msg, $num

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

if(CheckBlock($connect) != 1 && CheckBlock($connect) != 3) {
    $query = "select no,nation from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $title = str_replace("|", " ", $title);
    $msg = str_replace("|", " ", $msg);
    $reply = str_replace("|", " ", $reply);
    $title = trim($title);
    $msg = trim($msg);
    $reply = trim($reply);

    $nation = getNation($connect, $me['nation']);

    //새글 추가시
    if($num == -1 && $title != "" && $msg != "") {
        $num = $nation['coreindex'] + 1;
        if($num >= 20) { $num = 0; }
        $msg = $title."|".$msg;
        $msg = addslashes(SQ2DQ($msg));
        $date = date('Y-m-d H:i:s');
        $query = "update nation set coreboard{$num}='$msg',coreboard{$num}_who='{$me['no']}',coreboard{$num}_when='$date' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update nation set coreindex='$num' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //$num에 댓글시
    } elseif($num >= 0 && $reply != "") {
        $newmsg = $nation["coreboard{$num}"]."|".$me['no']."|".$reply;
        $newmsg = addslashes(SQ2DQ($newmsg));

        $query = "update nation set coreboard{$num}='$newmsg' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

//echo "<script>location.replace('b_chiefboard.php');</script>";
echo 'b_chiefboard.php';//TODO:replace
?>

</body>
</html>

