<?php
require('_common.php');
require(ROOT.'/f_config/config.php');
require(ROOT.W.F_FUNC.W.'class._DB.php');
require(ROOT.W.F_FUNC.W.'class._JSON.php');

class _Chat {
    public static function SetChat($type, $name, $msg) {
        $filename = ROOT.W.D_CHAT.W.$type."Chat.txt";
        AppendToFile($filename, "{$name}: {$msg}\r\n");
    }

    public static function GetChat($type, $size=10) {
        $filename = ROOT.W.D_CHAT.W.$type."Chat.txt";
        $content = ReadToFileBackward($filename, $size*100);
        $msgs = explode("\r\n", $content);
        $count = count($msgs) - 1;
        $start = $count - $size;
        if($start < 0) $start = 0;
        for($i = $start; $i < $count; $i++) {
            $newMsg[] = htmlspecialchars($msgs[$i]);
        }
        return $newMsg;
    }
}


