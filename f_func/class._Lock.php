<?
require_once('_common.php');
require_once(ROOT.'/f_config/config.php');

class _Lock {
    private static $l = ROOT.W.'lock.txt';

    public static function Busy() {
        $fp = fopen(_Lock::$l, 'r');
        $lock = fread($fp, 1);
        fclose($fp);

        if($lock == 1) return true;
        else return false;
    }

    private static function LockFile() {
        $fp = fopen(_Lock::$l, 'r');
        $lock = fread($fp, 1);
        fclose($fp);

        if($lock == 1) return false;

        $fp = fopen(_Lock::$l, 'w');
        if(!flock($fp, LOCK_EX)) { return false; }
        fwrite($fp, '1');
        fclose($fp);
        flock($fp, LOCK_UN);

        return true;
    }

    private static function UnlockFile() {
        $fp = fopen(_Lock::$l, 'r');
        $lock = fread($fp, 1);
        fclose($fp);

        if($lock == 0) return false;

        $fp = fopen(_Lock::$l, 'w');
        if(!flock($fp, LOCK_EX)) { return false; }
        fwrite($fp, '0');
        fclose($fp);
        flock($fp, LOCK_UN);

        return true;
    }

    public static function Lock() {
/*
        // 키 생성
        $key = fileinode(_Lock::$l);
        // 뮤텍스 획득
        $mutex = sem_get($key);
        // 락 획득
        sem_acquire($mutex);
*/
        // 파일에 잠금 걸기
        return _Lock::LockFile();
    }

    public static function Unlock() {
        // 파일에 잠금 풀기
        $res = _Lock::UnlockFile();
/*
        // 락 해제
        sem_release($mutex);
*/
        return $res;
    }
}

?>
