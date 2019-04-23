<?php
namespace sammo;

include "lib.php";
include "func.php";
// $turn, $command, $cost, $name, $nationname, $note, $double, $third, $fourth
$turn = Util::getReq('turn', 'array_int');
$command = Util::getReq('command', 'int', 0);
$cost = Util::getReq('cost', 'int');
$name = Util::getReq('name');
$nationname = Util::getReq('nationname', 'string', '');
$note = Util::getReq('note', 'string', '');
$double = Util::getReq('double', 'int', 0);
$third = Util::getReq('third', 'int', 0);
$fourth = Util::getReq('fourth', 'int', 0);

extractMissingPostToGlobals();

if(!$turn){
    $turn = [0];
}

'@phan-var int $double';
'@phan-var int $third';
'@phan-var int $fourth';

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

if($command < 0) { $command = 0; }
if($double < 0) { $double = 0; }
if($third < 0)  { $third = 0; }
if($fourth < 0) { $fourth = 0; }
if($command > 99) { $command = 0; }
if($double > 9999) { $double = 9999; }
if($fourth > 9999) { $fourth = 9999; }

$comStr = EncodeCommand($fourth, $third, $double, $command);

// 건국
if($command == 46) {
    $name = StringUtil::neutralize($name);
    if($name == "") { $name = "무명"; }

    $db->update('general', [
        'makenation'=>$name
    ], 'owner=%i', $userID);

    $query = [];
    foreach($turn as $turnIdx){
        $query['turn'.$turnIdx] = $comStr;
    }
    $db->update('general', $query, 'owner=%i', $userID);
    header('Location:./', true, 303);
    die();

}

//통합제의
if($command == 53) {
    $query = "select nation,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $nationname = StringUtil::neutralize($nationname, 18);
        if($nationname == "") { $nationname = "무명"; }
        
        $db->update('general', [
            'makenation'=>$nationname
        ], 'level>=5 and nation=%i', $me['nation']);

        $count = count($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$comStr}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    header('location:b_chiefcenter.php', true, 303);
    die();
}

//불가침
if($command == 61) {
    $query = "select nation,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $count = count($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$comStr}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    header('location:b_chiefcenter.php', true, 303);
    die();
} 

//포상, 몰수, 발령, 항복권고, 원조
//선전포고, 종전, 파기, 초토화, 천도, 증축, 감축
//백성동원, 수몰, 허보, 피장파장, 의병모집, 이호경식, 급습
//국기변경
if($command == 23 || $command == 24 || $command == 27 || $command == 51 || $command == 52 || $command > 60) {
    $query = "select no,nation,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if(($command == 23 || $command == 24 || $command == 27) && $me['no'] == $third) {
    	// 자기자신에게 악용 금지
    } elseif($me['level'] >= 5) {
        $count = count($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$comStr}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    header('location:b_chiefcenter.php', true, 303);
    die();  
}

//일반 턴
$query = [];
foreach($turn as $turnIdx){
    $query['turn'.$turnIdx] = $comStr;
}
$db->update('general', $query, 'owner=%i', $userID);
header('Location:./', true, 303);

