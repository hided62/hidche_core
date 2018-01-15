<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    echo "<script>location.replace('_admin4.php');</script>";
}

switch($btn) {
    case "블럭 해제":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set block=0 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "1단계 블럭":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set block=1,killturn=24 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "select user_id from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gen = MYDB_fetch_array($result);
            $uid[$i] = $gen['user_id'];
        }
        $connect = dbConn("sammo");
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($uid); $i++) {
            //블럭정보
            $query = "update MEMBER set block_num=block_num+1,block_date='$date' where id='$uid[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "2단계 블럭":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set block=2,killturn=24 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "select user_id from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gen = MYDB_fetch_array($result);
            $uid[$i] = $gen['user_id'];
        }
        $connect = dbConn("sammo");
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($uid); $i++) {
            //블럭정보
            $query = "update MEMBER set block_num=block_num+1,block_date='$date' where id='$uid[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "3단계 블럭":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set block=3,killturn=24 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "select user_id from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gen = MYDB_fetch_array($result);
            $uid[$i] = $gen['user_id'];
        }
        $connect = dbConn("sammo");
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($uid); $i++) {
            //블럭정보
            $query = "update MEMBER set block_num=block_num+1,block_date='$date' where id='$uid[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "무한삭턴":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set killturn=8000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "강제 사망":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set turn0=0,killturn=0,turntime='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
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
echo '_admin4.php'; //TODO:replace

