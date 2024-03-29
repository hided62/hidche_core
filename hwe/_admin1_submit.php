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

$msg = Util::getPost('msg');
$btn = Util::getPost('btn');
$log = Util::getPost('log');
$starttime = Util::getPost('starttime', 'string', (new \DateTime())->format('Y-m-d H:i:s'));
$maxgeneral = Util::getPost('maxgeneral', 'int', GameConst::$defaultMaxGeneral);
$maxnation = Util::getPost('maxnation', 'int', GameConst::$defaultMaxNation);
$startyear = Util::getPost('startyear', 'int', GameConst::$defaultStartYear);

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$admin = getAdmin();

switch ($btn) {
    case "변경":
        $gameStor->msg = $msg;
        break;
    case "로그쓰기":
        pushGlobalHistoryLog(["<R>★</><S>{$log}</>"]);
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
        $admin = $gameStor->getValues(['turntime', 'turnterm', 'year', 'startyear', 'month']);
        switch ($btn) {
        case   "1분턴": $turnterm = 1; break;
        case   "2분턴": $turnterm = 2; break;
        case   "5분턴": $turnterm = 5; break;
        case  "10분턴": $turnterm = 10; break;
        case  "20분턴": $turnterm = 20; break;
        case  "30분턴": $turnterm = 30; break;
        case  "60분턴": $turnterm = 60; break;
        case "120분턴": $turnterm = 120; break;
        default: throw new \Exception("알 수 없는 턴 기간");
        }
        ServerTool::changeServerTerm($turnterm);
        break;
}

header('location:_admin1.php');