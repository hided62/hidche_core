<?php

require_once('_common.php');
require_once(ROOT.'/f_config/config.php');
require_once(ROOT.W.F_FUNC.W.'class._DB.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');

class _Log {
    private static $flagLog = true;

    public static function SetLog($type, $log) {
        $filename = ROOT.W.D_LOG.W.$type."Log.txt";
        if(_Log::$flagLog) AppendToFile($filename, $log."\n");
    }

    public static function GetWorldLog($type, $size=10) {
        $filename = ROOT.W.D_LOG.W.$type."Log.txt";
        if(_Log::$flagLog) {
            $content = ReadToFileBackward($filename, $size*150);
            $logs = explode("\n", $content);
            $count = count($logs) - 1;
            $start = $count - $size;
            if($start < 0) $start = 0;
            for($i = $start; $i < $count; $i++) {
                $newLog[] = $logs[$i];
            }
        }
        return $newLog;
    }

    public static function DecodeLog($log) {
        $log = str_replace("<R>", "<font color=red>", $log);
        $log = str_replace("<B>", "<font color=blue>", $log);
        $log = str_replace("<G>", "<font color=green>", $log);
        $log = str_replace("<M>", "<font color=magenta>", $log);
        $log = str_replace("<C>", "<font color=cyan>", $log);
        $log = str_replace("<L>", "<font color=limegreen>", $log);
        $log = str_replace("<S>", "<font color=skyblue>", $log);
        $log = str_replace("<O>", "<font color=orange>", $log);
        $log = str_replace("<D>", "<font color=darkorange>", $log);
        $log = str_replace("<Y>", "<font color=yellow>", $log);
        $log = str_replace("<W>", "<font color=white>", $log);
        $log = str_replace("</>", "</font>", $log);
        return $log;
    }
}


