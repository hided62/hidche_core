<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

if(Session::getUserGrade() < 5) {
    //echo "<script>location.replace('_admin4.php');</script>";
    echo '_admin4.php';//TODO:debug all and replace
}

//NOTE: 왜 기능이 admin2와 admin4가 같이 있는가? 
//NOTE: 왜 블럭 시 admin4에선 금쌀을 없애지 않는가?
switch($btn) {
    case "블럭 해제":
        DB::db()->query('update general set block=0 where no IN %li', $genlist);
        break;
    case "1단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = DB::db();
        $db->query('update general set block=1,killturn=24 where no IN %li',$genlist);
        //FIXME: subquery로 하는게 더 빠를 듯.
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "2단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = DB::db();
        $db->query('update general set block=2,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "3단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = DB::db();
        $db->query('update general set block=3,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "무한삭턴":
        DB::db()->query('update general set killturn=8000 where no IN %li',$genlist);
        break;
    case "강제 사망":
        $date = date('Y-m-d H:i:s');
        DB::db()->query('update general set turn0=0,killturn=0,turntime=%s where no IN %li',$date, $genlist);
        break;
    case "메세지 전달":
    //TODO:새 갠메 시스템으로 변경
        $date = date('Y-m-d H:i:s');
        $msg;
        for($i=0; $i < sizeof($genlist); $i++) {
        }
        break;
}

//echo "<script>location.replace('_admin4.php');</script>";
echo '_admin4.php'; //TODO:debug all and replace

