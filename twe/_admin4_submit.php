<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where no_member='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    //echo "<script>location.replace('_admin4.php');</script>";
    echo '_admin4.php';//TODO:debug all and replace
}

//NOTE: 왜 기능이 admin2와 admin4가 같이 있는가? 
//NOTE: 왜 블럭 시 admin4에선 금쌀을 없애지 않는가?
switch($btn) {
    case "블럭 해제":
        getDB()->query('update general set block=0 where no IN %li', $genlist);
        break;
    case "1단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = getDB();
        $db->query('update general set block=1,killturn=24 where no IN %li',$genlist);
        //FIXME: subquery로 하는게 더 빠를 듯.
        $uid = $db->queryFirstColumn('select user_id from general where no IN %li', $genlist);
        getRootDB()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "2단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = getDB();
        $db->query('update general set block=2,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select user_id from general where no IN %li', $genlist);
        getRootDB()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "3단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = getDB();
        $db->query('update general set block=3,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select user_id from general where no IN %li', $genlist);
        getRootDB()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "무한삭턴":
        getDB()->query('update general set killturn=8000 where no IN %li',$genlist);
        break;
    case "강제 사망":
        $date = date('Y-m-d H:i:s');
        getDB()->query('update general set turn0=0,killturn=0,turntime=%s where no IN %li',$date, $genlist);
        break;
    case "메세지 전달":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date',newmsg=1 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
}

//echo "<script>location.replace('_admin4.php');</script>";
echo '_admin4.php'; //TODO:debug all and replace

