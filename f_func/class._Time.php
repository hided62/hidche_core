<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

class _Time {
    public static function DateToday() {
        return date('Y-m-d');
    }

    public static function DatetimeNow() {
        return date('Y-m-d H:i:s');
    }

    public static function DatetimeFromNowMinute($minute) {
        return date('Y-m-d H:i:s', strtotime("{$minute} minutes"));
    }

    public static function DatetimeFromNowSecond($second) {
        return date('Y-m-d H:i:s', strtotime("{$second} seconds"));
    }

    public static function DatetimeFromMinute($date, $minute) {
        return date('Y-m-d H:i:s', strtotime($date) + $minute*60);
    }

    public static function DatetimeFromSecond($date, $second) {
        return date('Y-m-d H:i:s', strtotime($date) + $second);
    }

    public static function CutSecond($date) {
        $date[17] = '0';
        $date[18] = '0';
        return $date;
    }

    public static function CutMinute($date) {
        $date[14] = '0';
        $date[15] = '0';
        $date[17] = '0';
        $date[18] = '0';
        return $date;
    }

    public static function HourMinuteSecond($second) {
        return date('H:i:s', strtotime('00:00:00') + $second);
    }
}


