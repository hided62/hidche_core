<?php
include "lib.php";
include "func.php";

$connect = dbConn();
increaseRefresh($connect, "자동", 2);
checkTurn($connect);
