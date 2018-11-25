<?php
namespace sammo;

class TimeUtil
{
    public static function today()
    {
        $obj = new \DateTime();
        return $obj->format('Y-m-d');
    }

    public static function now(bool $withFraction=false)
    {
        $obj = new \DateTime();
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function nowAddDays($day, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->add(static::secondsToDateInterval($day * 3600 * 24));
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function nowAddHours($hour, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->add(static::secondsToDateInterval($hour * 3600));
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function nowAddMinutes($minute, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->add(static::secondsToDateInterval($minute * 60));
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function nowAddSeconds($second, bool $withFraction=false)
    {
        $obj = new \DateTime();
        $obj->add(static::secondsToDateInterval($second));
        if(!$withFraction){
            return $obj->format('Y-m-d H:i:s');
        }
        return $obj->format('Y-m-d H:i:s.u');
    }

    public static function secondsToDateTime(float $fullSeconds, bool $isDateTimeImmutable=false): \DateTimeInterface{
        $seconds = floor($fullSeconds);
        $fraction = $fullSeconds - $seconds;

        $interval = new \DateInterval("PT0S");
        $interval->s = $seconds;
        $interval->f = $fraction;

        if($isDateTimeImmutable){
            $dateTime = new \DateTimeImmutable("@0");
            return $dateTime->add($interval);
        }

        $dateTime = new \DateTime("@0");
        $dateTime->add($interval);
        return $dateTime;
    }

    public static function secondsToDateInterval(float $fullSeconds): \DateInterval{
        $d0 = new \DateTime("@0");

        return static::secondsToDateTime($fullSeconds, true)->diff($d0);
    }

    public static function DateTimeToSeconds(\DateTimeInterface $dateTime): float{
        $d0 = new \DateTimeImmutable("@0");

        return static::DateIntervalToSeconds($dateTime->diff($d0));
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
