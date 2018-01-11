<?php
//임시 땜빵 리팩터링.
require_once "lib.php";

function dbConnRoot() {
    global $connect, $HTTP_COOKIE_VARS;
    $f = @file("../d_setting/set.php") or Error("set.php파일이 없습니다. DB설정을 먼저 하십시요!");
    for($i=1; $i<= 4; $i++) $f[$i] = trim(str_replace("\n","",$f[$i]));
    if(!$connect) $connect = @MYDB_connect($f[1],$f[2],$f[3]) or Error("DB 접속시 에러가 발생했습니다");
    @MYDB_select_db($f[4], $connect) or Error("DB Select 에러가 발생했습니다","");
    return $connect;
}
