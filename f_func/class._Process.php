<?php
require_once('_common.php');
require_once(ROOT.'/f_config/config.php');
require_once(ROOT.W.F_FUNC.W.'class._Lock.php');

class _Process {
    private static $mutexLog = false;

    public static function ProcessingMutex($DB) {
        // 어디선가 처리중이면 탈출
        if(_Lock::Busy() == true) return false;
        // 1명 외 접근 금지
        if(_Lock::Lock() != true) return false;

        _Process::MutexLog('뮤텍스 진입');

        // 처리
        _Process::Processing($DB);

        _Process::MutexLog('뮤텍스 탈출');

        // 접근 금지 해제
        if(_Lock::UnLock() != true) return false;

        return true;
    }

    private static function Processing($DB) {
    }

    private static function MutexLog($log) {
        if(_Process::$mutexLog) _Log::SetLog('mutex', $log);
    }
}

?>
