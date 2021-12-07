<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

WebUtil::requireAJAX();

$session = Session::requireLogin([])->setReadOnly();

if($session->userGrade < 5){
    Json::die([
        'result'=>false,
        'reason'=>'운영자 권한이 없습니다.'
    ]);
}

// 외부 파라미터
// action : 처리종류
// user_id : 유저 이름
// param : 추가 파라미터

$action = Util::getPost('action');
$userID = Util::getPost('user_id');
$param = Util::getPost('param');

if(!$action){
    Json::die([
        'result'=>false,
        'reason'=>'action 미설정'
    ]);
}


$db = RootDB::db();

if($action == 'allow_login'){
    $param = Util::toInt($param);
    if($param === null && $param !== 0 && $param !== 1){
        Json::die([
            'result'=>false,
            'reason'=>'올바르지 않은 param'
        ]);
    }

    $param = $param?'Y':'N';
    $db->update('system',[
        'LOGIN'=>$param
    ], true);
    Json::die([
        'result'=>true
    ]);
}

if($action == 'allow_join'){
    $param = Util::toInt($param);
    if($param === null && $param !== 0 && $param !== 1){
        Json::die([
            'result'=>false,
            'reason'=>'올바르지 않은 param'
        ]);
    }

    $param = $param?'Y':'N';
    $db->update('system',[
        'REG'=>$param
    ], true);
    Json::die([
        'result'=>true
    ]);
}

if($action == 'scrub_deleted'){
    $deleteUntil = TimeUtil::today();
    $db->delete('member', 'delete_after < %s', $deleteUntil);
    $cnt = $db->affectedRows();

    Json::die([
        'result'=>true,
        'affected'=>$cnt
    ]);
}

if($action == 'scrub_icon'){
    $deleteUntil = strtotime("-1 month");
    $usedIcon = [];
    foreach($db->queryFirstColumn('SELECT picture from member where imgsvr = 1') as $icon){
        $icon = explode('?', $icon)[0];
        $usedIcon[$icon]=$icon;
    }

    $cnt = 0;

    foreach(glob(AppConf::getUserIconPathFS().'/*.{webp,jpg,png,gif}', GLOB_BRACE) as $filepath){
        $filename = basename($filepath);

        if (array_key_exists($filename, $usedIcon)) {
            continue;
        }

        $mtime = filemtime($filepath);
        if($mtime > $deleteUntil){
            continue;
        }

        @unlink($filepath);
        $cnt++;
    }

    Json::die([
        'result'=>true,
        'affected'=>$cnt
    ]);
}

if($action == 'scrub_old_user'){
    $deleteUntil = TimeUtil::nowAddMinutes(-60*24*30*6);
    $targetUser = [];
    $members = $db->query('SELECT member.no, max(member_log.date) as loginDate from member
    left join member_log on member.`NO` = member_log.member_no and
    (member_log.action_type="login" or member_log.action_type="reg") group by member.no');
    foreach($members as $member){
        if($member['loginDate'] === null){
            $targetUser[] = $member['no'];
            continue;
        }

        if($member['loginDate'] <= $deleteUntil){
            $targetUser[] = $member['no'];
        }
    }

    if(count($targetUser) == 0){
        $cnt = 0;
    }
    else{
        $db->delete('member', 'no IN %li', $targetUser);
        $cnt = $db->affectedRows();
    }

    Json::die([
        'result'=>true,
        'affected'=>$cnt
    ]);
}

//여기부터는 무조건 멤버에 대한 항목임
if(!$userID){
    Json::die([
        'result'=>false,
        'reason'=>'userID가 지정되지 않았습니다. action:'.$action
    ]);
}

$targetGrade = $db->queryFirstField('SELECT `grade` FROM `member` WHERE `no` = %i', $userID);
if($targetGrade === null){
    Json::die([
        'result'=>false,
        'reason'=>'해당하는 유저가 없습니다.'
    ]);
}

if($targetGrade >= $session->userGrade){
    Json::die([
        'result'=>false,
        'reason'=>'자신과 같거나 높은 권한의 유저를 변경할 수 없습니다.'
    ]);
}




if($action == 'delete'){
    $db->delete('member', 'no = %i', $userID);
    $cnt = $db->affectedRows();

    if(!$cnt){
        Json::die([
            'result'=>false,
            'reason'=>'유저가 없습니다.'
        ]);
    }

    Json::die([
        'result'=>true
    ]);
}

if($action == 'reset_pw'){

    $newPassword = Util::randomStr(6);
    $tmpPassword = Util::hashPassword(RootDB::getGlobalSalt(), $newPassword);
    $newSalt = bin2hex(random_bytes(8));
    $newFinalPassword = Util::hashPassword($newSalt, $tmpPassword);

    $db->update('member', [
        'pw'=>$newFinalPassword,
        'salt'=>$newSalt
    ],'no=%i', $userID);
    $cnt = $db->affectedRows();

    if(!$cnt){
        Json::die([
            'result'=>false,
            'reason'=>'유저가 없습니다.'
        ]);
    }

    Json::die([
        'result'=>true,
        'detail'=>"비밀번호가 {$newPassword}로 초기화되었습니다."
    ]);
}


if($action == 'block'){
    $param = Util::toInt($param);
    if($param === null){
        Json::die([
            'result'=>false,
            'reason'=>'올바르지 않은 param'
        ]);
    }

    if($param <= 0){
        $param = 50*365;
    }

    $db->update('member', [
        'grade'=>0,
        'block_date'=>$db->sqleval('DATE_ADD(now(), INTERVAL %i DAY)', $param)
    ], 'no = %i', $userID);
    $cnt = $db->affectedRows();

    if(!$cnt){
        Json::die([
            'result'=>false,
            'reason'=>'유저가 없습니다.'
        ]);
    }

    Json::die([
        'result'=>true
    ]);
}

if($action == 'unblock'){
    $db->update('member', [
        'grade'=>1,
        'block_date'=>null
    ], 'no = %i', $userID);
    $cnt = $db->affectedRows();

    if(!$cnt){
        Json::die([
            'result'=>false,
            'reason'=>'유저가 없습니다.'
        ]);
    }

    Json::die([
        'result'=>true
    ]);
}

if($action == 'set_userlevel'){
    $param = Util::toInt($param);
    if($param === null || $param < 1){
        Json::die([
            'result'=>false,
            'reason'=>'올바르지 않은 param'
        ]);
    }

    if($param >= $session->userGrade){
        Json::die([
            'result'=>false,
            'reason'=>'관리자보다 같거나 높은 등급을 설정할 수 없습니다.'
        ]);
    }

    $db->update('member', [
        'grade'=>$param,
    ], 'no = %i', $userID);
    $cnt = $db->affectedRows();

    if(!$cnt){
        Json::die([
            'result'=>false,
            'reason'=>'실행되지 않았습니다.'
        ]);
    }

    Json::die([
        'result'=>true
    ]);
}

Json::die([
    'result'=>false,
    'reason'=>'알 수 없는 명령입니다. action:'.$action
]);