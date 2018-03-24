<?php
require(__dir__.'/../vendor/autoload.php');



function checkScoutAvailable($messageInfo, $general, $srcGeneral, $startyear, $year){
    $nationID = $messageInfo['src']['nationID'];

    $srcNation = DB::db()->queryFirstRow('SELECT `level`, `scout` FROM `nation` WHERE `nation` = %i', $nationID);

    $realNationID = $srcGeneral['nation'];

    

    if($general['level'] == 12) {
        return [false, "군주입니다."];
    } 
    if(!$srcNation || $srcNation['level'] == 0) {
        return [false, "없는 국가이거나 방랑군입니다."];
    } 
    if($realNationID != $nationID){
        return [false, "권유자의 소속이 바뀌었습니다."];
    }
    if($srcNation['scout'] != 0) {
        return [false, "임관 금지중입니다."];
    } 
    if($year < $startyear+3) {
        return [false, "초반 제한중입니다."];
    } 
    if(strpos($general['nations'], ",{$nationID},") > 0) {
        return [false, "이미 임관했었던 국가입니다."];
    }

    return [true, null];
    
}

function acceptScout($messageInfo, $general, $msgResponse){
    $me = $general;
    $you = DB::db()->queryFirstRow('SELECT `no`, `name`, `nation` FROM `general` WHERE `no` = %i', $messageInfo['src']['id']);

    list($startyear, $year, $month, $killturn) = Util::convertDictToArray(DB::db()->queryFirstRow('SELECT `startyear`, `year`, `month`, `killturn` FROM `game` LIMIT 1'), ['startyear', 'year', 'month', `killturn`]);

    list($avaliableScout, $reason) = checkScoutAvailable($messageInfo, $general, $you, $startyear, $year);

    if(!$msgResponse || !$avaliableScout){
        return declineScout($messageInfo, $reason);
    }

    $nation = getNationStaticInfo($messageInfo['src']['nationID']);
    $generalID = $me['no'];
    $nationID = $nation['nation'];
    $nationName = $nation['name'];
    $myName = $me['name'];
    
    $mylog = [];
    $youlog = [];
    $alllog = [];

    $youlog[] = "<C>●</><Y>{$myName}</> 등용에 성공했습니다.";
    $alllog[] = "<C>●</>{$month}월:<Y>{$myName}</>(이)가 <D><b>{$nationName}</b></>(으)로 <S>망명</>하였습니다.";
    $mylog[] = "<C>●</><D>{$nationName}</>(으)로 망명하여 수도로 이동합니다.";
    addHistory($you, "<C>●</>{$year}년 {$month}월:<Y>{$myName}</> 등용에 성공");
    addHistory($me, "<C>●</>{$year}년 {$month}월:<D>{$nationName}</>(으)로 망명");

    $me['nations'] .= "{$nationID},";

    $updateMe = [
        'belong'=>1,
        'nation'=>$nationID,
        'level'=>1,
        'city'=>$nation['capital'],
        'troop'=>0
    ];


    //처리가 조금 다름.

    $db = DB::db();


    if($me['level'] > 0){
        $updateOldNation = [
            'totaltech' => $db->sqleval('`tech`*greatest(10, `gennum`-1)'),
            'gennum' => $db->sqleval('greatest(10, `gennum`-1)')
        ];

        if($me['gold'] > 1000){
            $updateOldNation['gold'] = $db->sqleval('`gold` + %i', $me['gold'] - 1000);
            $updateMe['gold'] = 1000;
        }
        if($me['rice'] > 1000){
            $updateOldNation['rice'] = $db->sqleval('`rice` + %i', $me['rice'] - 1000);
            $updateMe['rice'] = 1000;
        }

        $updateMe['betray'] = $db->sqleval('`betray` + 1');
        $updateMe['dedication'] = $db->sqleval('dedication*(1-0.1*betray)');
        $updateMe['experience'] = $db->sqleval('experience*(1-0.1*betray)');

        $db->update('nation', $updateOldNation, 'nation = %i', $messageInfo['dest']['nationID']);
    }
    else{
        $updateMe['dedication'] = $db->sqleval('dedication + 100');
        $updateMe['experience'] = $db->sqleval('experience + 100');
    }

    if($me['npc'] < 2){
        $updateMe['killturn'] = $killturn;
    }

    $db->update('general', $updateMe, 'no = %i', $generalID);

    $db->query('UPDATE nation set '.
        'gennum = greatest(10, (SELECT count(`id`) from general where `nation` = %i_nation)), '.
        'totaltech = tech * greatest(10, (SELECT count(`id`) from general where `nation` = %i_nation)) '.
        'where nation=%i_nation', ['nation'=>$nationID]);

    //태수 군사 시중 해제
    switch($me['level']) {
    case 4:
        $db->query('update city set gen1=0 where gen1=%i', $generalID);
        break;
    case 3:
        $db->query('update city set gen3=0 where gen2=%i', $generalID);
        break;
    case 2:
        $db->query('update city set gen3=0 where gen2=%i', $generalID);
        break;
    }

    $db->query('UPDATE general left join troop on troop.troop = general.troop set general.troop = 0 where troop.no = %i', $generalID);
    $db->query('delete from troop where no = %i', $generalID);

    $db->query('UPDATE `message` SET `valid_until`=\'1234-11-22 11:22:33\' WHERE `id` = %i', $messageInfo['id']);
    $msg = "{$nationName}(으)로 등용 제의 수락";
    sendRawMessage('private', false, $general['no'], $messageInfo['src'], $messageInfo['dest'], $msg, null, null, ['parent'=>$messageInfo['id']]);

    pushGenLog($me, $mylog);
    pushGenLog($you, $youlog);
    pushAllLog($alllog);
    pushHistory($alllog);

    return [true, 'success'];
}

function declineScout($messageInfo, $reason=null){
    $me = [
        'no'=>$messageInfo['dest']['id'],
        'name'=>$messageInfo['dest']['name']
    ];
    $you = ['no'=>$messageInfo['src']['id']];

    $mylog = [];
    $youlog = [];
    

    $nationName = $messageInfo['src']['nation'];
    if($reason){
        $mylog[] = "<C>●</>{$reason} 등용 수락 불가.";
        $msg = "{$nationName}(으)로 등용 제의 수락 불가";
    }
    else{
        $youlog[] = "<C>●</><Y>{$me['name']}</>(이)가 등용을 거부했습니다.";
        $mylog[] = "<C>●</><D>{$nationName}</>(으)로 망명을 거부했습니다.";
        $msg = "{$nationName}(으)로 등용 제의 거부";
    }
    
    $db = DB::db();
    $db->query('UPDATE `message` SET `valid_until`=\'1234-11-22 11:22:33\' WHERE `id` = %i', $messageInfo['id']);

    sendRawMessage('private', false, $general['no'], $messageInfo['src'], $messageInfo['dest'], $msg, null, null, ['parent'=>$messageInfo['id']]);

    pushGenLog($me, $mylog);
    pushGenLog($you, $youlog);

    return [true, 'success'];
}

function acceptAlly($messageInfo, $general){

}

function declineAlly($messageInfo, $general){

}

function acceptBreakAlly($messageInfo, $general){

}

function declineBreakAlly($messageInfo, $general){

}

function acceptStopWar($messageInfo, $general){

}

function declineStopWar($messageInfo, $general){

}


function acceptMergeNations($messageInfo, $general){

}

function declineMergeNations($messageInfo, $general){

}


function acceptSurrender($messageInfo, $general){

}

function declineSurrender($messageInfo, $general){

}