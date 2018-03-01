<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$admin = getAdmin($connect);

$query = "select userlevel from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 4) {
    //echo "<script>location.replace('_admin3.php');</script>";
    echo '_admin3.php';//TODO:debug all and replace
}

$generalID = getGeneralID();

switch($btn) {
    case "무기지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $you = getDB()->queryFirstRow('select `no`, `nation` from `general` where `no` = %i', $genlist[$i]);
            if($weap == 0) { $msg = "무기 회수!"; }
            else { $msg = getWeapName($weap)." 지급!"; }
            // 상대에게 발송
            sendMessage('private', ['id'=>$generalID, 'nation_id'=>0], ['id'=>$you['no'], 'nation_id'=>$you['nation']], $msg);
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set weap='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set weap='$weap' where no='$genlist[$i]' and weap<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "책지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $you = getDB()->queryFirstRow('select `no`, `nation` from `general` where `no` = %i', $genlist[$i]);
            if($weap == 0) { $msg = "서적 회수!"; }
            else { $msg = getBookName($weap)." 지급!"; }
            // 상대에게 발송
            sendMessage('private', ['id'=>$generalID, 'nation_id'=>0], ['id'=>$you['no'], 'nation_id'=>$you['nation']], $msg);
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set book='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set book='$weap' where no='$genlist[$i]' and book<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "말지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $you = getDB()->queryFirstRow('select `no`, `nation` from `general` where `no` = %i', $genlist[$i]);
            if($weap == 0) { $msg = "말 회수!"; }
            else { $msg = getHorseName($weap)." 지급!"; }
            // 상대에게 발송
            sendMessage('private', ['id'=>$generalID, 'nation_id'=>0], ['id'=>$you['no'], 'nation_id'=>$you['nation']], $msg);
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set horse='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set horse='$weap' where no='$genlist[$i]' and horse<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "도구지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $you = getDB()->queryFirstRow('select `no`, `nation` from `general` where `no` = %i', $genlist[$i]);
            if($weap == 0) { $msg = "특수도구 회수!"; }
            else { $msg = getItemName($weap)." 지급!"; }
            // 상대에게 발송
            sendMessage('private', ['id'=>$generalID, 'nation_id'=>0], ['id'=>$you['no'], 'nation_id'=>$you['nation']], $msg);
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set item='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set item='$weap' where no='$genlist[$i]' and item<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "특별회원 해제":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set userlevel=1 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "메세지 전달":
    //TODO:새 갠메 시스템으로 변경
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $you = getDB()->queryFirstRow('select `no`, `nation` from `general` where `no` = %i', $genlist[$i]);
            // 상대에게 발송
            sendMessage('private', ['id'=>$generalID, 'nation_id'=>0], ['id'=>$you['no'], 'nation_id'=>$you['nation']], $msg);
        }
        break;
}

//echo "<script>location.replace('_admin3.php');</script>";
echo '_admin3.php';//TODO:debug all and replace

