<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();

$db = DB::db();

TurnExecutionHelper::executeAllCommand();
