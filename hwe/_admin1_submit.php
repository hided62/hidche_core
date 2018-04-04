<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

$admin = getAdmin();

if(Session::getUserGrade() < 5) {
    //echo "<script>location.replace('_admin1.php');</script>";
    echo '_admin1.php';//TODO:debug all and replace
}

$db = DB::db();
$connect=$db->get();

switch($btn) {
    case "변경":
        $msg = addslashes(SQ2DQ($msg));
        $query = "update game set msg='$msg'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "요청":
        $query = $q;
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "로그쓰기":
        $lognum = $admin['historyindex'] + 1;
        if($lognum >= 29) { $lognum = 0; }
        $history[0] = "<R>★</><S>{$log}</>";
        pushWorldHistory($history);
        break;
    case "변경1":
        $query = "update game set starttime='$starttime'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경2":
        $query = "update game set maxgeneral='$maxgeneral'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경3":
        $query = "update game set maxnation='$maxnation'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경4":
        $query = "update game set startyear='$startyear'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "변경5":
        $query = "update game set normgeneral='$gen_rate'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "1분턴":
    case "2분턴":
    case "5분턴":
    case "10분턴":
    case "20분턴":
    case "30분턴":
    case "60분턴":
    case "120분턴":
        switch($btn) {
        case   "1분턴": $turnterm = 1; break;
        case   "2분턴": $turnterm = 2; break;
        case   "5분턴": $turnterm = 5; break;
        case  "10분턴": $turnterm = 10; break;
        case  "20분턴": $turnterm = 20; break;
        case  "30분턴": $turnterm = 30; break;
        case  "60분턴": $turnterm = 60; break;
        case "120분턴": $turnterm = 120; break;
        }
        $unit = $turnterm * 60;
        $turn = ($admin['year'] - $admin['startyear']) * 12 + $admin['month'] - 1;
        $starttime = date("Y-m-d H:i:s", strtotime($admin['turntime']) - $turn * $unit);
        $starttime = cutTurn($starttime, $turnterm);
        $query = "update game set turnterm='$turnterm',starttime='$starttime'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 턴시간이 길어지는 경우 랜덤턴 배정
        if($turnterm < $admin['turnterm']) {
            $query = "select no from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $turntime = getRandTurn($turnterm);
                $query = "update general set turntime='$turntime' where no='{$gen['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        // 턴시간이 너무 멀리 떨어진 선수 제대로 보정
        } else {
            $query = "select no,turntime from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $num = floor((strtotime($gen['turntime']) - strtotime($admin['turntime'])) / $unit);
                if($num > 0) {
                    $gen['turntime'] = date("Y-m-d H:i:s", strtotime($gen['turntime']) - $unit * $num);
                    $query = "update general set turntime='{$gen['turntime']}' where no='{$gen['no']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                }
            }
        }
        $history[] = "<R>★</>턴시간이 <C>$btn</>으로 변경됩니다.";
        pushWorldHistory($history);
        break;
}

//echo "<script>location.replace('_admin1.php');</script>";
echo '_admin1.php';//TODO:debug all and replace

