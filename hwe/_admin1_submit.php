<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if ($session->userGrade < 5) {
    header('location:_admin1.php');
    die();
}

$v = new Validator($_POST);
$v->rule('integer', [
    'maxgeneral',
    'minutes2'
])->rule('dateFormat', [
    'starttime'
], 'Y-m-d H:i:s');
if (!$v->validate()) {
    Error($v->errorStr());
}

$msg = Util::getReq('msg');
$btn = Util::getReq('btn');
$log = Util::getReq('log');
$starttime = Util::getReq('starttime', 'string', (new \DateTime())->format('Y-m-d H:i:s'));
$maxgeneral = Util::getReq('maxgeneral', 'int', GameConst::$defaultMaxGeneral);
$maxnation = Util::getReq('maxnation', 'int', GameConst::$defaultMaxNation);
$startyear = Util::getReq('startyear', 'int', GameConst::$defaultStartYear);

extractMissingPostToGlobals();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

$admin = getAdmin();

switch ($btn) {
    case "변경":
        $gameStor->msg = $msg;
        break;
    case "로그쓰기":
        $lognum = $admin['historyindex'] + 1;
        if ($lognum >= 29) {
            $lognum = 0;
        }
        pushWorldHistory(["<R>★</><S>{$log}</>"]);
        break;
    case "변경1":
        $gameStor->starttime = (new \DateTime($starttime))->format('Y-m-d H:i:s');
        break;
    case "변경2":
        $gameStor->maxgeneral = $maxgeneral;
        break;
    case "변경3":
        $gameStor->maxnation = $maxnation;
        break;
    case "변경4":
        $gameStor->startyear = $startyear;
        break;
    case "1분턴":
    case "2분턴":
    case "5분턴":
    case "10분턴":
    case "20분턴":
    case "30분턴":
    case "60분턴":
    case "120분턴":
        switch ($btn) {
        case   "1분턴": $turnterm = 1; break;
        case   "2분턴": $turnterm = 2; break;
        case   "5분턴": $turnterm = 5; break;
        case  "10분턴": $turnterm = 10; break;
        case  "20분턴": $turnterm = 20; break;
        case  "30분턴": $turnterm = 30; break;
        case  "60분턴": $turnterm = 60; break;
        case "120분턴": $turnterm = 120; break;
        }
        $unit = $turnterm * 60;
        $turn = ($admin['year'] - $admin['startyear']) * 12 + $admin['month'] - 1;
        $starttime = date("Y-m-d H:i:s", strtotime($admin['turntime']) - $turn * $unit);
        $starttime = cutTurn($starttime, $turnterm);
        $gameStor->turnterm = $turnterm;
        $gameStor->starttime = $starttime;
        // 턴시간이 길어지는 경우 랜덤턴 배정
        if ($turnterm < $admin['turnterm']) {
            $query = "select no from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
            $count = MYDB_num_rows($result);
            for ($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $turntime = getRandTurn($turnterm);
                $query = "update general set turntime='$turntime' where no='{$gen['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
            }
            // 턴시간이 너무 멀리 떨어진 선수 제대로 보정
        } else {
            $query = "select no,turntime from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
            $count = MYDB_num_rows($result);
            for ($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $num = intdiv((strtotime($gen['turntime']) - strtotime($admin['turntime'])), $unit);
                if ($num > 0) {
                    $gen['turntime'] = date("Y-m-d H:i:s", strtotime($gen['turntime']) - $unit * $num);
                    $query = "update general set turntime='{$gen['turntime']}' where no='{$gen['no']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
                }
            }
        }
        
        pushWorldHistory(["<R>★</>턴시간이 <C>$btn</>으로 변경됩니다."]);
        break;
}

header('location:_admin1.php');