<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$weap = Util::getReq('weap', 'int');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireLogin()->loginGame()->setReadOnly();

if($session->userGrade < 5) {
    header('location:_admin2.php');
}

$generalID = $session->generalID;
if(!$generalID){
    header('location:_admin2.php');
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

$src = MessageTarget::buildQuick($session->generalID);

switch($btn) {
    case "전체 접속허용":
        $db->update('general', [
            'con'=>0
        ], true);
        break;
    case "전체 접속제한":
        $db->update('general', [
            'con'=>1000
        ], true);
        break;
    case "블럭 해제":
        $db->update('general', [
            'block'=>0
        ], '`no` IN %li', $genlist);
        DB::db()->query('update general set block=0 where no IN %li', $genlist);
        break;
    case "1단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db->update('general', [
            'block'=>1,
            'killturn'=>24
        ], '`no` IN %li', $genlist);
        $uid = $db->queryFirstColumn('SELECT `owner` FROM general WHERE `no` IN %li', $genlist);
        RootDB::db()->update('member',[
            'block_num'=>$db->sqleval('block_num+1'),
            'block_date'=>$date
        ], 'id IN %li', $uid);
        break;
    case "2단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db->update('general', [
            'gold'=>0,
            'rice'=>0,
            'block'=>2,
            'killturn'=>24
        ], '`no` IN %li', $genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        RootDB::db()->update('member',[
            'block_num'=>$db->sqleval('block_num+1'),
            'block_date'=>$date
        ], 'id IN %li', $uid);
        break;
    case "3단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db->update('general', [
            'gold'=>0,
            'rice'=>0,
            'block'=>3,
            'killturn'=>24
        ], '`no` IN %li', $genlist);
        $uid = $db->queryFirstColumn('SELECT `owner` from general where no IN %li', $genlist);
        RootDB::db()->update('member',[
            'block_num'=>$db->sqleval('block_num+1'),
            'block_date'=>$date
        ], 'id IN %li', $uid);
        break;
    case "무한삭턴":
        $db->update('general', [
            'killturn'=>8000
        ], '`no` IN %li', $genlist);
        break;
    case "강제 사망":
        $date = date('Y-m-d H:i:s');
        $db->update('general', [
            'turn0'=>0,
            'killturn'=>0,
            'turntime'=>$date,
        ], '`no` IN %li', $genlist);
        break;
    case "특기 부여":
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
        $text = "특기 부여!";

        foreach($db->query("SELECT `no`,leader,power,intel,dex0,dex10,dex20,dex30,dex40 FROM general WHERE `no` IN %li", $genlist) as $general){    
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($general['no']), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);

            $specialWar = SpecialityConst::pickSpecialWar($general);
            $db->update('general', [
                'specage2'=>$db->sqleval('age'),
                'special2'=>$specialWar
            ], 'no=%i', $general['no']);
            $specialWarName = SpecialityConst::WAR[$specialWar][0];
            pushGeneralHistory($general, "<C>●</>{$year}년 {$month}월:특기 【<b><C>{$specialWarName}</></b>】(을)를 습득");
            pushGenLog($general, ["<C>●</>특기 【<b><L>{$specialWarName}</></b>】(을)를 익혔습니다!"]);
        }
        
        break;
    case "경험치1000":
        $text = $btn." 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'experience'=>$db->sqleval('experience+1000')
        ], '`no` IN %li', $genlist);

        break;
    case "공헌치1000":
        $text = $btn." 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'dedication'=>$db->sqleval('dedication+1000')
        ], '`no` IN %li', $genlist);

        break;
    case "보숙10000":
        $text = "보병숙련도+10000 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'dex0'=>$db->sqleval('dex0+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "궁숙10000":
        $text = "궁병숙련도+10000 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'dex10'=>$db->sqleval('dex10+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "기숙10000":
        $src = MessageTarget::buildQuick($session->generalID);
        $text = "기병숙련도+10000 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'dex20'=>$db->sqleval('dex20+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "귀숙10000":
        $src = MessageTarget::buildQuick($session->generalID);
        $text = "귀병숙련도+10000 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'dex30'=>$db->sqleval('dex30+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "차숙10000":
        $src = MessageTarget::buildQuick($session->generalID);
        $text = "차병숙련도+10000 지급!";
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        $db->update('general',[
            'dex40'=>$db->sqleval('dex40+10000')
        ], '`no` IN %li', $genlist);
        break;
    case "접속 허용":
        $db->update('general',[
            'con'=>0
        ], '`no` IN %li', $genlist);
        break;
    case "접속 제한":
        $db->update('general',[
            'con'=>1000
        ], '`no` IN %li', $genlist);
        break;
    case "메세지 전달":
        $text = $msg;
        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }
        break;
    case "무기지급":

        if($weap == 0) {
            $text = "무기 회수!";
        }
        else { 
            $text = getWeapName($weap)." 지급!"; 
        }

        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }

        if($weap == 0){
            $db->update('general', [
                'weap'=>0
            ], '`no` IN %li', $genlist);
        }
        else{
            $db->update('general', [
                'weap'=>$weap
            ], '`no` IN %li AND weap < %i', $genlist, $weap);
        }
        break;
    case "책지급":
        if($weap == 0) {
            $text = "책 회수!";
        }
        else { 
            $text = getBookName($weap)." 지급!"; 
        }

        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }

        if($weap == 0){
            $db->update('general', [
                'book'=>0
            ], '`no` IN %li', $genlist);
        }
        else{
            $db->update('general', [
                'book'=>$weap
            ], '`no` IN %li AND book < %i', $genlist, $weap);
        }
        break;
    case "말지급":
        if($weap == 0) {
            $text = "말 회수!";
        }
        else { 
            $text = getHorseName($weap)." 지급!"; 
        }

        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }

        if($weap == 0){
            $db->update('general', [
                'horse'=>0
            ], '`no` IN %li', $genlist);
        }
        else{
            $db->update('general', [
                'horse'=>$weap
            ], '`no` IN %li AND horse < %i', $genlist, $weap);
        }
        break;
    case "도구지급":
        if($weap == 0) {
            $text = "특수도구 회수!";
        }
        else { 
            $text = getItemName($weap)." 지급!"; 
        }

        foreach($genlist as $generalID){
            $msg = new Message(Message::MSGTYPE_PRIVATE, $src, MessageTarget::buildQuick($generalID), $text, new \DateTime(), new \DateTime('9999-12-31'), []);
            $msg->send(true);
        }

        if($weap == 0){
            $db->update('general', [
                'item'=>0
            ], '`no` IN %li', $genlist);
        }
        else{
            $db->update('general', [
                'item'=>$weap
            ], '`no` IN %li AND item < %i', $genlist, $weap);
        }
        break;
    case "NPC해제":
        $db->update('general', [
            'npc'=>1
        ], '`no` IN %li', $genlist);
        break;
    case "하야입력":
        $db->update('general', [
            'turn0'=>'00000000000045'
        ], '`no` IN %li', $genlist);
        break;
    case "방랑해산":
        $db->update('general', [
            'turn0'=>'00000000000047',
            'turn1'=>'00000000000056'
        ], '`no` IN %li', $genlist);
        break;
    case "NPC설정":
        $db->update('general', [
            'npc'=>2
        ], '`no` IN %li', $genlist);
        break;
    case "00턴":
        $turnterm = $gameStor->turnterm;

        foreach($genlist as $generalID){
            $turntime = getRandTurn($turnterm);
            $cutTurn = cutTurn($turntime, $turnterm);
            $db->update('general', [
                'turntime'=>$curTurn
            ], '`no` IN %li', $genlist);
        }
        break;
    case "랜덤턴":
        foreach($genlist as $generalID){
            $turntime = getRandTurn($turnterm);
            $db->update('general', [
                'turntime'=>$turntime
            ], '`no` IN %li', $genlist);
        }
        break;
}

header('location:_admin2.php');