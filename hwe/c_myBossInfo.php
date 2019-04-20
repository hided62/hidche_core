<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $level, $genlist, $outlist

$btn = Util::getReq('btn');
$level = Util::getReq('level', 'int');
$genlist = Util::getReq('genlist', 'int');
$outlist = Util::getReq('outlist', 'int');
$citylist = Util::getReq('citylist', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

$admin = $gameStor->getValues(['startyear','year','month','scenario']);

$query = "select no,nation,level from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);
$meLevel = $me['level'];

$query = "select no from general where nation='{$me['nation']}' and level=12";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$ruler = MYDB_fetch_array($result);

//수뇌가 아니면 아무것도 할 수 없음
if($meLevel < 5){
    header('location:b_myBossInfo.php');
    exit();
}

if($btn == "임명") {
    if($genlist==0){
        $general = [];
    }
    else{
        $general = $db->queryFirstRow('SELECT `no`,nation,`level`,leader,`power`,intel FROM general WHERE no = %i', $genlist);
    }

    if($genlist != 0 && !$general){
        header('location:b_myBossInfo.php');
        exit();
    }

    //임명할사람이 군주이면 불가, 내가 수뇌부이어야함, 공석아닌때는 국가가 같아야함
    if($meLevel < 5 || ($genlist != 0 && $general['nation'] != $me['nation']) || ($genlist != 0 && $general['level'] == 12)) {
        header('location:b_myBossInfo.php');
        exit();
    }
} elseif($btn == "추방") {
    if(!$outlist){
        header('location:b_myBossInfo.php');
        exit();
    }
    $query = "select no,name,gold,rice,nation,troop,level,npc,picture,imgsvr,permission,penalty,belong from general where no='$outlist'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if(!$general){
        header('location:b_myBossInfo.php');
        exit();
    }

    //추방할사람이 군주이면 불가, 내가 수뇌부이어야함, 공석아닌때는 국가가 같아야함
    if($general['level'] == 12 || $meLevel < 5 || ($general['nation'] != $me['nation'] && $outlist != 0)) {
        header('location:b_myBossInfo.php');
        exit();
    }

    //추방할사람이 외교권자이면 불가
    $permission = checkSecretPermission($general);
    if($permission == 4){
        header('location:b_myBossInfo.php');
        exit();
    }

    //NPC 부대장 추방 불가
    if($general['npc'] == 5){
        header('location:b_myBossInfo.php');
        exit();
    }
}

//나와 대상 장수는 국가가 같아야 함
if($genlist != 0 && $me['nation'] != $general['nation']){
    header('location:b_myBossInfo.php');
    exit();
}

if($btn == "추방") {
    $query = "select name,l{$meLevel}set,color from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $alllog = [];

    //이미 지정했다면 무시
    if($nation["l{$meLevel}set"] == 0 && $general['level'] > 0 && $general['level'] < 12) {
        $dipcount1 = $db->queryFirstField('SELECT count(no) FROM diplomacy WHERE me=%i AND state>=3 AND state<=4', $general['nation']);
        $dipcount2 = $db->queryFirstField('SELECT count(no) FROM diplomacy WHERE me=%i AND state>=5 AND state<=6', $general['nation']);

        $gold = 0;
        $rice = 0;
        // 금쌀1000이상은 남김
        if($general['gold'] > GameConst::$defaultGold) {
            $gold = $general['gold'] - GameConst::$defaultGold;
            $general['gold'] = GameConst::$defaultGold;
        }
        if($general['rice'] > GameConst::$defaultRice) {
            $rice = $general['rice'] - GameConst::$defaultRice;
            $general['rice'] = GameConst::$defaultRice;
        }

        if($dipcount1 > 0) {
            $josaYi = JosaUtil::pick($general['name'], '이');
            $alllog[] = "<C>●</>{$admin['month']}월:통합에 반대하던 <Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>에서 <R>숙청</>당했습니다.";
            $log[] = "<C>●</>통합에 반대하다가 <D><b>{$nation['name']}</b></>에서 <R>숙청</>당했습니다.";

            // 재야로, 국가 무소속으로
            $query = "update general set level=0,nation=0,belong=0,makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($dipcount2 > 0) {
            $josaYi = JosaUtil::pick($general['name'], '이');
            $alllog[] = "<C>●</>{$admin['month']}월:합병에 반대하던 <Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>에서 <R>숙청</>당했습니다.";
            $log[] = "<C>●</>합병에 반대하다가 <D><b>{$nation['name']}</b></>에서 <R>숙청</>당했습니다.";

            // 재야로, 국가 무소속으로
            $query = "update general set level=0,nation=0,belong=0,makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $josaYi = JosaUtil::pick($general['name'], '이');
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>에서 <R>추방</>당하였습니다.";
            $log[] = "<C>●</><D><b>{$nation['name']}</b></>에서 <R>추방</>당하였습니다.";

            // 재야로, 국가 무소속으로, 명성/공헌 N*10%감소
            if($admin['year'] <= $admin['startyear'] && $general['npc'] < 2){
                $db->update('general', [
                    'level'=>0,
                    'nation'=>0,
                    'belong'=>0,
                    'permission'=>'normal',
                ], 'no=%i', $general['no']);
            }
            else{
                $db->update('general', [
                    'level'=>0,
                    'permission'=>'normal',
                    'nation'=>0,
                    'belong'=>0,
                    'makelimit'=>12,
                    'gold'=>$general['gold'],
                    'rice'=>$general['rice'],
                    'experience'=>$db->sqleval('experience * (1 - 0.1*betray)'),
                    'dedication'=>$db->sqleval('dedication * (1 - 0.1*betray)'),
                    'betray'=>$db->sqleval('betray + 1'),
                ], 'no=%i', $general['no']);
            }
        }
        // 부대 처리
        $query = "select no from troop where troop='{$general['troop']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $troop = MYDB_fetch_array($result);

        //부대장일 경우
        if($troop['no'] == $general['no']) {
            // 모두 탈퇴
            $query = "update general set troop='0' where troop='{$general['troop']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 부대 삭제
            $query = "delete from troop where troop='{$general['troop']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set troop='0' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 도시관직해제
        $query = "update city set gen1='0' where gen1='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen2='0' where gen2='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen3='0' where gen3='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general['npc'] >= 2 && ($admin['scenario'] < 100 || rand()%100 == 0)) {
            switch(rand()%5) {
            case 0: $str = "날 버리다니... 곧 전장에서 복수해주겠다..."; break;
            case 1: $str = "추방이라... 내가 무얼 잘못했단 말인가..."; break;
            case 2: $str = "어디 추방해가면서 잘되나 보자... 꼭 복수하겠다..."; break;
            case 3: $str = "인덕이 제일이거늘... 추방이 웬말인가... 저주한다!"; break;
            case 4: $str = "날 추방했으니 그 복수로 적국에 정보를 팔아 넘겨야겠군요. 그럼 이만."; break;
            }

            $src = new MessageTarget(
                $general['no'], 
                $general['name'],
                $general['nation'],
                $nation['name'],
                $nation['color'],
                GetImageURL($general['imgsvr'], $general['picture'])
            );
            $msg = new Message(
                Message::MSGTYPE_PUBLIC, 
                $src,
                $src,
                $str,
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send();
        }

        if($admin['year'] < $admin['startyear']+3) {
            //초반엔 군주 부상 증가(엔장 임관지양)
            $db->update('general', [
                'injury'=>$db->sqleval('injury + 1'),
            ], 'no=%i', $ruler['no']);

            $db->update('nation', [
                'gennum'=>$db->sqleval('gennum - 1'),
                'gold'=>$db->sqleval('gold + %i', $gold),
                'rice'=>$db->sqleval('rice + %i', $rice),
            ], 'nation = %i', $general['nation']);
        } else {
            //이번분기는 추방불가(초반 제외)
            $db->update('nation', [
                "l{$meLevel}set"=>1,
                'gennum'=>$db->sqleval('gennum - 1'),
                'gold'=>$db->sqleval('gold + %i', $gold),
                'rice'=>$db->sqleval('rice + %i', $rice),
            ], 'nation = %i', $general['nation']);
        }

        list($year, $month) = $gameStor->getValuesAsArray(['year','month']);
        pushGeneralHistory($general, "<C>●</>{$year}년 {$month}월:<D>{$nation['name']}</>에서 추방됨");

        pushGenLog($general, $log);
        pushGeneralPublicRecord($alllog, $year, $month);
    }
    header('location:b_myBossInfo.php');
    die();
}

if($btn == "임명" && $level >= 5 && $level <= 11) {
    $query = "select l{$level}set,level from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

	// 임명가능 레벨
    $lv = getNationChiefLevel($nation['level']);
    
    //이미 지정했다면 무시
    if($nation["l{$level}set"] == 0 && $lv <= $level) {
        //기존 장수 일반으로
        $query = "update general set level=1 where nation='{$me['nation']}' and level='$level'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        if($genlist != 0) {
            $valid = 0;
            switch($level) {
            case 10: if($general['power'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
            case  9: if($general['intel'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
            case  8: if($general['power'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
            case  7: if($general['intel'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
            case  6: if($general['power'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
            case  5: if($general['intel'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
            default: $valid = 1; break;
            }
            if($valid == 1) {
                // 신임 장수의 원래 자리 해제
                $query = "update city set gen1=0 where gen1='$genlist'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $query = "update city set gen2=0 where gen2='$genlist'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $query = "update city set gen3=0 where gen3='$genlist'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                //신임 장수
                $query = "update general set level='$level' where no='$genlist'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                //이번분기는 불가
                $query = "update nation set l{$level}set=1 where nation='{$me['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }
    header('location:b_myBossInfo.php');
    die();
} 


if($btn == "임명" && $level >= 2 && $level <= 4 && $citylist > 0) {
    switch($level) {
    case 4: $genlv = 'gen1'; $genlvset = 'gen1set'; break;
    case 3: $genlv = 'gen2'; $genlvset = 'gen2set'; break;
    case 2: $genlv = 'gen3'; $genlvset = 'gen3set'; break;
    }

    $query = "select {$genlv} from city where nation='{$me['nation']}' and city='$citylist'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);
    if(!$city){
        header('location:b_myBossInfo.php');
        die();
    }
    $oldlist = $city[$genlv];

    if($oldlist != 0) {
        //기존 장수 일반으로
        $db->update('general', [
            'level'=>1
        ], 'no=%i', $oldlist);
        //기존 자리 공석으로
        $db->update('city', [
            $genlv=>0
        ], 'city = %i AND nation = %i', $citylist , $me['nation']);
    }
    if($genlist != 0) {
        $valid = 0;
        switch($level) {
        case  4: if($general['power'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
        case  3: if($general['intel'] >= GameConst::$chiefStatMin) { $valid = 1; } break;
        default: $valid = 1; break;
        }

        if($valid == 1) {
            // 신임 장수의 원래 자리 해제
            $db->update('city', [
                'gen1'=>0,
            ], 'gen1=%i', $genlist);
            $db->update('city', [
                'gen2'=>0,
            ], 'gen2=%i', $genlist);
            $db->update('city', [
                'gen3'=>0,
            ], 'gen3=%i', $genlist);

            //신임 장수
            $db->update('city', [
                $genlv=>$genlist,
                $genlvset=>1
            ], 'city=%i AND nation=%i', $citylist, $general['nation']);
            if($db->affectedRows() > 0){
                $db->update('general',[
                    'level'=>$level
                ], 'no=%i', $genlist);
            }
        }
    }
    header('location:b_myBossInfo.php');
    die();
}


header('location:b_myBossInfo.php');


