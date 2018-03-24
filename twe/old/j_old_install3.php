<?php
include "lib.php";
include "func.php";

if(Session::getUserGrade(true) < 5){
    die('관리자 아님');
}

$connect=dbConn();

LogHistory(1);

//echo "<script>location.replace('index.php');</script>";
echo 'index.php'; //TODO:debug all and replace
