<?php
namespace sammo;

class TimeUtil
{
    public static function DateToday()
    {
        return date('Y-m-d');
    }

    public static function DatetimeNow()
    {
        return date('Y-m-d H:i:s');
    }

    public static function DatetimeFromNowDay($day)
    {
        return date('Y-m-d H:i:s', strtotime("{$day} days"));
    }

    public static function DatetimeFromNowHour($hour)
    {
        return date('Y-m-d H:i:s', strtotime("{$hour} hours"));
    }

    public static function DatetimeFromNowMinute($minute)
    {
        return date('Y-m-d H:i:s', strtotime("{$minute} minutes"));
    }

    public static function DatetimeFromNowSecond($second)
    {
        return date('Y-m-d H:i:s', strtotime("{$second} seconds"));
    }

    public static function DatetimeFromMinute($date, $minute)
    {
        return date('Y-m-d H:i:s', strtotime($date) + $minute*60);
    }

    public static function DatetimeFromSecond($date, $second)
    {
        return date('Y-m-d H:i:s', strtotime($date) + $second);
    }

    public static function CutSecond($date)
    {
        $date[17] = '0';
        $date[18] = '0';
        return $date;
    }

    public static function CutMinute($date)
    {
        $date[14] = '0';
        $date[15] = '0';
        $date[17] = '0';
        $date[18] = '0';
        return $date;
    }

    public static function HourMinuteSecond($second)
    {
        return date('H:i:s', strtotime('00:00:00') + $second);
    }

    /**
     * $baseYear, $baseMonth 부터 $afterMonth 개월 이내인지. $afterMonth 포함.
     * 
     */
    public static function IsRangeMonth(int $baseYear, int $baseMonth, int $afterMonth, int $askYear, int $askMonth):bool{
        if($baseMonth < 1 || $baseMonth > 12){
            throw new \InvalidArgumentException('개월이 올바르지 않음');
        }
        if($askMonth < 1 || $askMonth > 12){
            throw new \InvalidArgumentException('개월이 올바르지 않음');
        }

        $minMonth = $baseYear * 12 + $baseMonth;
        if($afterMonth < 0){
            $maxMonth = $minMonth;
            $minMonth = $maxMonth - $afterMonth;
        }

        $maxMonth = $minMonth + $afterMonth;
        $askMonth = $askYear * 12 + $askMonth;
        if($askMonth < $minMonth || $maxMonth < $askMonth){
            return false;
        }
        return true;
    }
}
