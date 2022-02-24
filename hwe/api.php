<?php

namespace sammo;

include "lib.php";
include "func.php";

APIHelper::launch(dirname(__FILE__), $_GET['path']??null);