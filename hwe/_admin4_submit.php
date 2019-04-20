<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if($session->userGrade < 5) {
    header('location:_admin4.php');
    die();
}

$btn = Util::getReq('btn');
$genlist = Util::getReq('genlist', 'array_int');
$msg = Util::getReq('msg','string', '메시지');

extractMissingPostToGlobals();

$db = DB::db();

//NOTE: 왜 기능이 admin2와 admin4가 같이 있는가? 
//NOTE: 왜 블럭 시 admin4에선 금쌀을 없애지 않는가?
switch($btn) {
    case "블럭 해제":
        DB::db()->query('update general set block=0 where no IN %li', $genlist);
        break;
    case "1단계 블럭":
        $date = TimeUtil::now();
        $db = DB::db();
        $db->query('update general set block=1,killturn=24 where no IN %li',$genlist);
        //FIXME: subquery로 하는게 더 빠를 듯.
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->query('update member set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "2단계 블럭":
        $date = TimeUtil::now();
        $db = DB::db();
        $db->query('update general set block=2,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->query('update member set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "3단계 블럭":
        $date = TimeUtil::now();
        $db = DB::db();
        $db->query('update general set block=3,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->query('update member set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "무한삭턴":
        DB::db()->query('update general set killturn=8000 where no IN %li',$genlist);
        break;
    case "강제 사망":
        $date = TimeUtil::now(true);
        $db->update('general', [
            'killturn'=>0,
            'turntime'=>$date,
        ], '`no` IN %li', $genlist);
        $db->update('general_turn', [
            'action'=>'휴식',
            'arg'=>'{}'
        ], 'general_id IN %li AND turn_idx = 0', $genlist);
        break;
    case "메세지 전달":
    //TODO:새 갠메 시스템으로 변경
        $date = TimeUtil::now();
        $src = MessageTarget::buildQuick($session->generalID);
        for($i=0; $i < count($genlist); $i++) {
            $msgObj = new Message(
                Message::MSGTYPE_PRIVATE,
                $src,
                MessageTarget::buildQuick($genlist[$i]),
                $msg,
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            if($msgObj){
                $msgObj->send(true);
            }
        }
        break;
}

header('location:_admin4.php');

