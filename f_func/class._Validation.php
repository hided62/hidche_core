<?php
require(__dir__.'/../vendor/autoload.php');
require_once(ROOT.'/f_func/class._String.php');

use utilphp\util as util;

class _Validation {
    public static function CheckID($id) {
        $len = strlen($id);
        if($len < 4 || $len > 12) { return 1; }
        for($i=0; $i < $len; $i++) {
            $ch = $id[$i];
            if(($ch < '0' || $ch > '9') && ($ch < 'a' || $ch > 'z')) {
                return 2;
            }
        }
        return 0;
    }

    public static function CheckPW($pw) {
        $len = strlen($pw);
        if($len < 4 || $len > 12) { return 1; }
        for($i=0; $i < $len; $i++) {
            $ch = $pw[$i];
            if(($ch < '0' || $ch > '9') && ($ch < 'a' || $ch > 'z')) {
                return 2;
            }
        }
        return 0;
    }

    public static function CheckPID($pid1, $pid2) {
        $len1 = strlen($pid1);
        $len2 = strlen($pid2);
        if($len1 != 6 || $len2 != 7) { return 1; }
        for($i=0; $i < $len1; $i++) {
            $ch = $pid1[$i];
            if(($ch < '0' || $ch > '9')) {
                return 2;
            }
        }
        for($i=0; $i < $len2; $i++) {
            $ch = $pid2[$i];
            if(($ch < '0' || $ch > '9')) {
                return 2;
            }
        }
        $year  = $pid1[0].$pid1[1];
        $month = $pid1[2].$pid1[3];
        $day   = $pid1[4].$pid1[5];
        $sex   = $pid2[0];
        if($year < 50) { return 3; }
        if($month < 1 || $month > 12) { return 3; }
        if($day < 1 || $day > 31) { return 3; }
        if($sex < 1 || $sex > 2) { return 3; }
        // 주민등록번호 체크
        $chk = 0;
        for($i=0; $i <= 5;$i++) {
            $chk += ($i%8 + 2) * $pid1[$i];
        }
        for($i=6; $i <= 11;$i++) {
            $chk += ($i%8 + 2) * $pid2[$i-6];
        }
        $chk = 11 - ($chk % 11);
        $chk = $chk % 10;
        if($chk != $pid2[6]) {
            return 3;
        }
        return 0;
    }

    public static function CheckBirth($pid1, $pid2) {
        $len1 = strlen($pid1);
        $len2 = strlen($pid2);
        if($len1 != 6 || $len2 != 1) { return 1; }
        for($i=0; $i < $len1; $i++) {
            $ch = $pid1[$i];
            if(($ch < '0' || $ch > '9')) {
                return 2;
            }
        }
        for($i=0; $i < $len2; $i++) {
            $ch = $pid2[$i];
            if(($ch < '0' || $ch > '9')) {
                return 2;
            }
        }
        $year  = $pid1[0].$pid1[1];
        $month = $pid1[2].$pid1[3];
        $day   = $pid1[4].$pid1[5];
        $sex   = $pid2[0];
        //if($year < 50) { return 3; }
        if($month < 1 || $month > 12) { return 3; }
        if($day < 1 || $day > 31) { return 3; }
        if($sex < 1 || $sex > 4) { return 3; }
        return 0;
    }

    public static function CheckName($name) {
//        $len = strlen($name);
        $len = _String::GetStrLen($name);
        if(strchr($name, "<")) { return 1; }
        if(strchr($name, ">")) { return 2; }
        if($len < 1 || $len > 6) { return $len; }
        return 0;
    }

    public static function CheckEmail($email) {
        if(util::ends_with($email, '@localhost')){
            return 0;
        }
        if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email)) {
            return 1;
        } else {
            return 0;
        }
    }
}


