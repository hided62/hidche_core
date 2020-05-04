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

$btn = Util::getReq('btn');
$minute = Util::getReq('minute', 'int');
$minute2 = Util::getReq('minute2', 'int');

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

        $gameStor->cacheValues(['turntime', 'starttime', 'tnmt_time']);
        $turntime = (new \DateTimeImmutable($gameStor->turntime))->sub(new \DateInterval("PT{$minute}M"));
        $starttime = (new \DateTimeImmutable($gameStor->starttime))->sub(new \DateInterval("PT{$minute}M"));
        $tnmt_time = (new \DateTimeImmutable($gameStor->tnmt_time))->sub(new \DateInterval("PT{$minute}M"));

        $gameStor->turntime = $turntime->format('Y-m-d H:i:s.u');
        $gameStor->starttime = $starttime->format('Y-m-d H:i:s');
        $gameStor->tnmt_time = $tnmt_time->format('Y-m-d H:i:s');

        $db->update('general', [
            'turntime' => $db->sqleval('DATE_SUB(turntime, INTERVAL %i MINUTE)', $minute)
        ], true);
        $db->update('auction', [
            'expire' => $db->sqleval('DATE_SUB(expire, INTERVAL %i MINUTE)', $minute)
        ], true);
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
        $gameStor->cacheValues(['turntime', 'starttime', 'tnmt_time']);
        $turntime = (new \DateTimeImmutable($gameStor->turntime))->add(new \DateInterval("PT{$minute}M"));
        $starttime = (new \DateTimeImmutable($gameStor->starttime))->add(new \DateInterval("PT{$minute}M"));
        $tnmt_time = (new \DateTimeImmutable($gameStor->tnmt_time))->add(new \DateInterval("PT{$minute}M"));

        $gameStor->turntime = $turntime->format('Y-m-d H:i:s.u');
        $gameStor->starttime = $starttime->format('Y-m-d H:i:s');
        $gameStor->tnmt_time = $tnmt_time->format('Y-m-d H:i:s');

        $db->update('general', [
            'turntime' => $db->sqleval('DATE_ADD(turntime, INTERVAL %i MINUTE)', $minute)
        ], true);
        $db->update('auction', [
            'expire' => $db->sqleval('DATE_ADD(expire, INTERVAL %i MINUTE)', $minute)
        ], true);
        if ($locked) {
            unlock();
        }
        break;
    case "토너분당김":
        $tnmt_time = new \DateTime($gameStor->tnmt_time);
        $tnmt_time->sub(new \DateInterval("PT{$minute2}M"));
        $gameStor->tnmt_time = $tnmt_time->format('Y-m-d H:i:s');
        break;
    case "토너분지연":
        $tnmt_time = new \DateTimeImmutable($gameStor->tnmt_time);
        $tnmt_time->add(new \DateInterval("PT{$minute2}M"));
        $gameStor->tnmt_time = $tnmt_time->format('Y-m-d H:i:s');
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
