<?php

namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getPost('btn');
$item = Util::getPost('item', 'string');
$genlist = Util::getPost('genlist', 'array_int');
$msg = Util::getPost('msg', 'string');

//로그인 검사
$session = Session::requireLogin()->loginGame()->setReadOnly();

if ($session->userGrade < 5) {
    header('location:_admin2.php');
}

$generalID = $session->generalID;
if (!$generalID) {
    header('location:_admin2.php');
    die();
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$src = MessageTarget::buildQuick($session->generalID);

$genObjList = [];
$env = [];
if ($genlist) {
    $genObjList = General::createGeneralObjListFromDB($genlist);
    $env = $gameStor->cacheAll();
}
switch ($btn) {
    case "전체 접속허용":
        $db->update('general', [
            'con' => 0
        ], true);
        break;
    case "전체 접속제한":
        $db->update('general', [
            'con' => 1000
        ], true);
        break;
    case "블럭 해제":
        $db->update('general', [
            'block' => 0
        ], '`no` IN %li', $genlist);
        DB::db()->query('update general set block=0 where no IN %li', $genlist);
        break;
    case "1단계 블럭":
        $date = TimeUtil::now();
        $db->update('general', [
            'block' => 1,
            'killturn' => 24
        ], '`no` IN %li', $genlist);
        $uid = $db->queryFirstColumn('SELECT `owner` FROM general WHERE `no` IN %li', $genlist);
        RootDB::db()->update('member', [
            'block_num' => $db->sqleval('block_num+1'),
            'block_date' => $date
        ], 'id IN %li', $uid);
        break;
    case "2단계 블럭":
        $date = TimeUtil::now();
        $db->update('general', [
            'gold' => 0,
            'rice' => 0,
            'block' => 2,
            'killturn' => 24
        ], '`no` IN %li', $genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->update('member', [
            'block_num' => $db->sqleval('block_num+1'),
            'block_date' => $date
        ], 'id IN %li', $uid);
        break;
    case "3단계 블럭":
        $date = TimeUtil::now();
        $db->update('general', [
            'gold' => 0,
            'rice' => 0,
            'block' => 3,
            'killturn' => 24
        ], '`no` IN %li', $genlist);
        $uid = $db->queryFirstColumn('SELECT `owner` from general where no IN %li', $genlist);
        RootDB::db()->update('member', [
            'block_num' => $db->sqleval('block_num+1'),
            'block_date' => $date
        ], 'id IN %li', $uid);
        break;
    case "무한삭턴":
        $db->update('general', [
            'killturn' => 8000
        ], '`no` IN %li', $genlist);
        break;
    case "강제 사망":
        $date = TimeUtil::now(true);
        $db->update('general', [
            'killturn' => 0,
            'turntime' => $date,
        ], '`no` IN %li', $genlist);
        $db->update('general_turn', [
            'action' => '휴식',
            'arg' => '{}',
            'brief' => '휴식',
        ], 'general_id IN %li AND turn_idx = 0', $genlist);
        break;
    case "경험치1000":
        $text = $btn . " 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'experience' => $db->sqleval('experience+1000')
        ], '`no` IN %li', $genlist);

        break;
    case "공헌치1000":
        $text = $btn . " 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'dedication' => $db->sqleval('dedication+1000')
        ], '`no` IN %li', $genlist);

        break;
    case "보숙10000":
        $text = "보병숙련도+10000 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'dex1' => $db->sqleval('dex1+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "궁숙10000":
        $text = "궁병숙련도+10000 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'dex2' => $db->sqleval('dex2+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "기숙10000":
        $src = MessageTarget::buildQuick($session->generalID);
        $text = "기병숙련도+10000 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'dex3' => $db->sqleval('dex3+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "귀숙10000":
        $src = MessageTarget::buildQuick($session->generalID);
        $text = "귀병숙련도+10000 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'dex4' => $db->sqleval('dex4+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "차숙10000":
        $src = MessageTarget::buildQuick($session->generalID);
        $text = "차병숙련도+10000 지급!";
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general', [
            'dex5' => $db->sqleval('dex5+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "접속 허용":
        $db->update('general', [
            'con' => 0
        ], '`no` IN %li', $genlist);
        break;
    case "접속 제한":
        $db->update('general', [
            'con' => 1000
        ], '`no` IN %li', $genlist);
        break;
    case "메세지 전달":
        $text = $msg ?? '';
        foreach ($genlist as $generalID) {
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        break;
    case "하야입력":
        $db->update('general_turn', [
            'action' => 'che_하야',
            'arg' => '{}',
            'brief' => '하야',
        ], 'general_id IN %li AND turn_idx = 0', $genlist);
        break;
    case "방랑해산":
        $db->update('general_turn', [
            'action' => 'che_방랑',
            'arg' => '{}',
            'brief' => '방랑',
        ], 'general_id IN %li AND turn_idx = 0', $genlist);
        $db->update('general_turn', [
            'action' => 'che_해산',
            'arg' => '{}',
            'brief' => '해산',
        ], 'general_id IN %li AND turn_idx = 1', $genlist);
        break;
}

header('location:_admin2.php');
