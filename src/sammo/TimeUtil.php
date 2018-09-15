<?php
namespace sammo;

class TimeUtil
{
    public static function DateToday()
    {
        $obj = new \DateTime();
        return $obj->format('Y-m-d');
    }

    public static function DatetimeNow(bool $withFraction=false)
    {
        $obj = new \DateTime();
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function DatetimeFromNowDay($day, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->modify("{$day} days");
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function DatetimeFromNowHour($hour, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->modify("{$hour} hours");
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function DatetimeFromNowMinute($minute, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->modify("{$minute} minutes");
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function DatetimeFromNowSecond($second, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->modify("{$second} seconds");
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function DatetimeFromMinute($date, $minute, bool $withFraction=false)
    {
        $obj = new \DateTime($date);
        $obj->modify("{$minute} minutes");
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function DatetimeFromSecond($date, $second, bool $withFraction=false)
    {
        $obj = new \DateTime($date);
        $obj->modify("{$second} seconds");
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function SecondsToDateInterval(float $fullSeconds): \DateInterval{
        $seconds = floor($fullSeconds);
        $fraction = $fullSeconds - $seconds;

        $interval = new \DateInterval("PT0S");
        $interval->s = $seconds;
        $interval->f = $fraction;

        $d0 = new \DateTimeImmutable("@0");
        $d1 = $d0->add($interval);

        return $d1->diff($d0);
    }

    public static function DateIntervalToSeconds(\DateInterval $interval): float{
        if($interval->days !== FALSE){
            $days = $interval->days;
        }
        else{
            if($interval->y != 0){
                throw new \InvalidArgumentException('Year argument conversion is not supported');
            }
            if($interval->m != 0){
                throw new \InvalidArgumentException('Month argument conversion is not supported');
            }
            $days = $interval->d;
        }
        
        $hours = $days * 24 + $interval->h;
        $minutes = $hours * 60 + $interval->i;
        $seconds = $minutes * 60 + $interval->s + $interval->f;
        
        return $seconds;
    }
}
