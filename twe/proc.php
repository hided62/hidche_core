<?php
namespace sammo;

include "lib.php";
include "func.php";

$connect = dbConn();
increaseRefresh("자동", 2);
checkTurn($connect);
