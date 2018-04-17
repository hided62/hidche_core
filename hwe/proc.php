<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();

$db = DB::db();
$connect=$db->get();

increaseRefresh("자동", 2);
checkTurn();
