<?php
// $turn, $command, $cost, $name, $nationname, $note, $double, $third, $fourth

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

if($command < 0) { $command = 0; }
if($double < 0) { $double = 0; }
if($third < 0)  { $third = 0; }
if($fourth < 0) { $fourth = 0; }
$double = round($double, 0);
$third = round($third, 0);
$fourth = round($fourth, 0);
if($command > 99) { $command = 0; }
if($double > 9999) { $double = 9999; }
if($third > 9999)  { $third = 9999; }
if($fourth > 9999) { $fourth = 9999; }

$comStr = EncodeCommand($fourth, $third, $double, $command);

// 건국
if($command == 46) {
    $name = addslashes(SQ2DQ($name));
    $name = str_replace("|", "", $name);
    $name = str_replace(" ", "", $name);
    $name = str_replace("　", "뷁", $name);
    if($name == "") { $name = "무명"; }
    $name = _String::SubStrForWidth($name, 0, 12);

    $query = "update general set makenation='{$name}' where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $count = sizeof($turn);
    $str = "con=con";
    for($i=0; $i < $count; $i++) {
        $str .= ",turn{$turn[$i]}='{$comStr}'";
    }
    $query = "update general set {$str} where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    echo "<script>location.replace('main.php');</script>";
//통합제의
} elseif($command == 53) {
    $query = "select nation,level from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $nationname = addslashes(SQ2DQ($nationname));
        $nationname = str_replace("|", "", $nationname);
        $nationname = str_replace(" ", "", $nationname);
        $nationname = str_replace("　", "뷁", $nationname);
        if($nationname == "") { $nationname = "무명"; }
        $nationname = _String::SubStrForWidth($nationname, 0, 12);

        $query = "update general set makenation='{$nationname}' where level>=5 and nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $count = sizeof($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$comStr}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    echo "<script>location.replace('b_chiefcenter.php');</script>";
//불가침
} elseif($command == 61) {
    $query = "select nation,level from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $note = addslashes(SQ2DQ($note));
        $note = str_replace("|", "", $note);
        $note = str_replace(" ", "", $note);
        $note = str_replace("　", "뷁", $note);
        $note = _String::SubStrForWidth($note, 0, 90);

        $query = "update diplomacy set reserved='{$note}' where me='{$me['nation']}' and you='$double'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $count = sizeof($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$comStr}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    echo "<script>location.replace('b_chiefcenter.php');</script>";
//포상, 몰수, 발령, 항복권고, 원조
//선전포고, 종전, 파기, 초토화, 천도, 증축, 감축
//백성동원, 수몰, 허보, 피장파장, 의병모집, 이호경식, 급습
//국기변경
} elseif($command == 23 || $command == 24 || $command == 27 || $command == 51 || $command == 52 || $command > 60) {
    $query = "select no,nation,level from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if(($command == 23 || $command == 24 || $command == 27) && $me['no'] == $third) {
    	// 자기자신에게 악용 금지
    } elseif($me['level'] >= 5) {
        $count = sizeof($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$comStr}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    echo "<script>location.replace('b_chiefcenter.php');</script>";
} else {
    $count = sizeof($turn);
    $str = "con=con";
    for($i=0; $i < $count; $i++) {
        $str .= ",turn{$turn[$i]}='{$comStr}'";
    }
    $query = "update general set {$str} where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    echo "<script>location.replace('main.php');</script>";
}


