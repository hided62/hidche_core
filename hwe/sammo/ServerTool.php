<?php

namespace sammo;

final class ServerTool
{
    private function __construct()
    {
    }

    public static function changeServerTerm(int $turnterm, ?bool $ignoreLock = null): ?string
    {
        // 하루에 연 단위로 게임 시간이 흘러야 함.
        if ((120 % $turnterm) != 0) {
            return 'invalid minute';
        }


        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $admin = $gameStor->getValues(['turntime', 'turnterm', 'year', 'startyear', 'month', 'isunited']);

        $reqGameLock = $admin['isunited'] != 2 && !$ignoreLock;

        $locked = false;
        if($reqGameLock){
            for ($i = 0; $i < 5; $i++) {
                $locked = tryLock();
                if ($locked) {
                    break;
                }
                usleep(500000);
            }

            if (!$locked) {
                return 'server busy';
            }
        }
        else{
            $locked = tryLock();
        }

        $oldunit = $admin['turnterm'] * 60;
        $unit = $turnterm * 60;

        if($unit == $oldunit){
            if($locked){
                unlock();
            }
            return null;
        }

        $unitDiff = $unit / $oldunit;

        $servTurnTime = new \DateTimeImmutable($admin['turntime']);
        foreach ($db->query('SELECT no,turntime FROM general') as $gen) {
            $genTurnTime = new \DateTimeImmutable($gen['turntime']);
            $timeDiff = TimeUtil::DateIntervalToSeconds($genTurnTime->diff($servTurnTime));
            $timeDiff *= $unitDiff;
            $newGenTurnTime = $servTurnTime->add(TimeUtil::secondsToDateInterval($timeDiff));

            $db->update('general', [
                'turntime' => $newGenTurnTime->format('Y-m-d H:i:s.u')
            ], 'no=%i', $gen['no']);
        }
        $turn = ($admin['year'] - $admin['startyear']) * 12 + $admin['month'] - 1;
        $starttime = $servTurnTime->sub(TimeUtil::secondsToDateInterval($turn * $unit))->format('Y-m-d H:i:s');
        $starttime = cutTurn($starttime, $turnterm, false);
        $gameStor->turnterm = $turnterm;
        $gameStor->starttime = $starttime;
        pushGlobalHistoryLog(["<R>★</>턴시간이 <C>{$turnterm}분</>으로 변경됩니다."]);

        if($locked){
            unlock();
        }

        return null;
    }
}
