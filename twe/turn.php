<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();
increaseRefresh($connect, "턴반복", 1);

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,msgindex,userlevel,con from general where user_id='$_SESSION['p_id']'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['userlevel'], $me['con'], $admin['conlimit']);
if($con >= 2) { echo "<script>window.top.main.location.replace('main.php');</script>"; exit(); }

switch($type) {
case 0:
/*
    for($i=0; $i < $sel; $i++) {
        $k = $i + $sel;
        $query = "update general set ";
        while(1) {
            $query .= "turn{$k}=turn{$i}";
            $k += $sel;
            if($k >= 24) break;
            $query .= ",";
        }
        $query .= " where user_id='$_SESSION['p_id']'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
*/
    $query = "update general set ";
    for($i=0; $i < $sel; $i++) {
        $k = $i + $sel;
        while(1) {
            $query .= "turn{$k}=turn{$i}";
            $k += $sel;
            if($i < $sel-1 || $k < 24) { $query .= ","; }
            if($k >= 24) break;
        }
    }
    $query .= " where user_id='$_SESSION['p_id']'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case 1:
    $i = 23 - $sel;
    $k = 23;
    $query = "update general set ";
    while(1) {
        $query .= "turn{$k}=turn{$i},";
        $i--; $k--;
        if($i < 0) break;
    }
    while(1) {
        $query .= "turn{$k}=0";
        $k--;
        if($k < 0) break;
        $query .= ",";
    }
    $query .= " where user_id='$_SESSION['p_id']'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case 2:
    $i = 0;
    $k = $sel;
    $query = "update general set ";
    while(1) {
        $query .= "turn{$i}=turn{$k},";
        $i++; $k++;
        if($k >= 24) break;
    }
    while(1) {
        $query .= "turn{$i}=0";
        $i++;
        if($i >= 24) break;
        $query .= ",";
    }
    $query .= " where user_id='$_SESSION['p_id']'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
}

echo "<script>location.replace('commandlist.php');</script>";
