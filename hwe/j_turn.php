<?php
namespace sammo;

include "lib.php";
include "func.php";



$session = Session::requireGameLogin(null)->setReadOnly();