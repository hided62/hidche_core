<?php
namespace sammo;

include "lib.php";
include "func.php";
// $title, $msg, $num
$title = Util::getReq('title');
$msg = Util::getReq('msg');
$num = Util::getReq('num', 'int');
$reply = Util::getReq('reply');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

if(getBlockLevel() != 1 && getBlockLevel() != 3) {
    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $title = str_replace("|", " ", $title);
    $msg = str_replace("|", " ", $msg);
    $reply = str_replace("|", " ", $reply);
    $title = trim($title);
    $msg = trim($msg);
    $reply = trim($reply);

    $nation = getNation($me['nation']);

    //새글 추가시
    if($num == -1 && $title != "" && $msg != "") {
        $num = $nation['boardindex'] + 1;
        if($num >= 20) { $num = 0; }
        $msg = $title."|".$msg;
        $msg = addslashes(SQ2DQ($msg));
        $date = TimeUtil::now();
        $query = "update nation set board{$num}='$msg',board{$num}_who='{$me['no']}',board{$num}_when='$date' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update nation set boardindex='$num' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //$num에 댓글시
    } elseif($num >= 0 && $reply != "") {
        $newmsg = $nation["board{$num}"]."|".$me['no']."|".$reply;
        $newmsg = addslashes(SQ2DQ($newmsg));

        $query = "update nation set board{$num}='$newmsg' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

header('location:b_nationboard.php');