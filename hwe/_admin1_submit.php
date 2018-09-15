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
        $admin = $gameStor->getDBValues(['turntime', 'turnterm', 'year', 'startyear', 'month']);
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
        $oldunit = $admin['turnterm'] * 60;
        $unit = $turnterm * 60;

        $unitDiff = $unit / $oldunit;

        $servTurnTime = new \DateTimeImmutable($admin['turntime']);
        foreach ($db->query('SELECT no,turntime FROM general') as $gen) {
            $genTurnTime = new \DateTimeImmutable($gen['turntime']);
            $timeDiff = TimeUtil::DateIntervalToSeconds($genTurnTime->diff($servTurnTime));
            $timeDiff *= $unitDiff;
            $newGenTurnTime = $servTurnTime->add(TimeUtil::secondsToDateInterval($timeDiff));

            $db->update('general', [
                'turntime'=>$newGenTurnTime->format('Y-m-d H:i:s.u')
            ], 'no=%i', $gen['no']);
        }
        $turn = ($admin['year'] - $admin['startyear']) * 12 + $admin['month'] - 1;
        $starttime = $servTurnTime->sub(TimeUtil::secondsToDateInterval($turn * $unit))->format('Y-m-d H:i:s');
        $starttime = cutTurn($starttime, $turnterm, false);
        $gameStor->turnterm = $turnterm;
        $gameStor->starttime = $starttime;
        pushWorldHistory(["<R>★</>턴시간이 <C>$btn</>으로 변경됩니다."]);
        break;
}

header('location:_admin1.php');