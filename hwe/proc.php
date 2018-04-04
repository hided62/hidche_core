<?php
namespace sammo;

include "lib.php";
include "func.php";

$db = DB::db();
$connect=$db->get();

increaseRefresh("자동", 2);
checkTurn();
