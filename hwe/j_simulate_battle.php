<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();
