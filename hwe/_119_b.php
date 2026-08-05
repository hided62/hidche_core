<?php

namespace sammo;

include "lib.php";
include "func.php";


$session = Session::requireGameLogin()->setReadOnly();
if ($session->userGrade < 4) {
    header('location:_119.php');
    die();
}

$v = new Validator($_POST);
$v->rule('integer', [
    'minute',
    'minutes2'
]);
if (!$v->validate()) {
    Error($v->errorStr());
}

$btn = Util::getPost('btn');
$minute = Util::getPost('minute', 'int');
$minute2 = Util::getPost('minute2', 'int');

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
switch ($btn) {
    case "분당김":
        $locked = false;
        for ($i = 0; $i < 10; $i++) {
            if (tryLock()) {
                $locked = true;
                break;
            }
            usleep(500000);
        }

        $clock = GameClock::fromStorage($gameStor);
        // 스케줄 전체를 벽시계에서 빼지 않고 논리 현재 tick만 앞으로 이동합니다.
        $clock->advance($gameStor, $clock->ticksFromMinutes($minute));
        if ($locked) {
            unlock();
        }
        break;
    case "분지연":
        $locked = false;
        for ($i = 0; $i < 5; $i++) {
            if (tryLock()) {
                $locked = true;
                break;
            }
            usleep(500000);
        }
        $clock = GameClock::fromStorage($gameStor);
        $clock->advance($gameStor, -$clock->ticksFromMinutes($minute));
        if ($locked) {
            unlock();
        }
        break;
    case "토너분당김":
        $clock = GameClock::fromStorage($gameStor);
        $gameStor->tnmt_time = Util::toInt($gameStor->tnmt_time) - $clock->ticksFromMinutes($minute2);
        break;
    case "토너분지연":
        $clock = GameClock::fromStorage($gameStor);
        $gameStor->tnmt_time = Util::toInt($gameStor->tnmt_time) + $clock->ticksFromMinutes($minute2);
        break;
    case "금지급":
        processGoldIncome();
        break;
    case "쌀지급":
        processRiceIncome();
        break;
    case "락걸기":
        for ($i = 0; $i < 10; $i++) {
            if (tryLock()) {
                $locked = true;
                break;
            }
            usleep(500000);
        }
        break;
    case "락풀기":
        unlock();
        break;
}

header('Location:_119.php', true, 303);
