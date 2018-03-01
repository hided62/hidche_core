<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select develcost,vote,votecomment from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,userlevel,vote,name,nation,horse,weap,book,item,npc from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($btn == "수정" && $me['userlevel'] >= 5) {
    if($title != "") {
        $vote = explode("|", $admin['vote']);
        $vote[0] = addslashes(SQ2DQ($title));
        $admin['vote'] = implode("|", $vote);
        $query = "update game set vote='{$admin['vote']}' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "추가" && $me['userlevel'] >= 5) {
    if($str != "") {
        $str = addslashes(SQ2DQ($str));
        $admin['vote'] .= "|{$str}";
        $query = "update game set vote='{$admin['vote']}' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "리셋" && $me['userlevel'] >= 5) {
    $query = "update game set voteopen=1,vote='',votecomment='' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "update general set vote='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "알림" && $me['userlevel'] >= 5) {
    $query = "update general set newvote='1' where vote=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "숨김" && $me['userlevel'] >= 5) {
    $query = "update game set voteopen=0 where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "전체통계만" && $me['userlevel'] >= 5) {
    $query = "update game set voteopen=1 where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "전부" && $me['userlevel'] >= 5) {
    $query = "update game set voteopen=2 where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "투표" && $me['vote'] == 0 && $sel > 0) {
    $develcost = $admin['develcost'] * 5;
    $query = "update general set gold=gold+{$develcost},vote='{$sel}' where owner='{$_SESSION['noMember']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $log = array();
    $log = uniqueItem($connect, $me, $log, 1);
    pushGenLog($me, $log);
} elseif($btn == "댓글" && $comment != "") {
    $comment = str_replace("|", " ", $comment);
    $comment = str_replace(":", " ", $comment);
    $comment = trim($comment);
    $comment = addslashes(SQ2DQ($comment));

    $nation = getNationStaticInfo($me['nation']);
    if($nation == null) { 
        $nation['name'] = "재야"; 
    }

    if($admin['votecomment'] != "") { $admin['votecomment'] .= "|"; }
    $admin['votecomment'] .= "{$nation['name']}:{$me['name']}:{$comment}";

    $query = "update game set votecomment='{$admin['votecomment']}' where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}
?>

<!--<script>location.replace('a_vote.php');</script> //TODO:debug all and replace -->
a_vote.php 
