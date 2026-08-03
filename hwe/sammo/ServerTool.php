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

        if($turnterm == $admin['turnterm']){
            if($locked){
                unlock();
            }
            return null;
        }

        $oldClock = GameClock::fromStorage($gameStor);
        $currentTick = $oldClock->nowTick();
        $currentDisplay = $oldClock->tickToDateTime($currentTick);
        $oldClock->persistTick($gameStor, $currentTick);
        $gameStor->turnterm = $turnterm;
        $gameStor->clock_base_time = TimeUtil::format(
            GameClock::baseTimeForProjection($currentDisplay, $currentTick, $turnterm),
            true,
        );
        pushGlobalHistoryLog(["<R>★</>턴시간이 <C>{$turnterm}분</>으로 변경됩니다."]);

        if($locked){
            unlock();
        }

        return null;
    }
}
