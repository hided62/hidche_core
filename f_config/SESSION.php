<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_func/class._Session.php');

$SESSION = new _Session();

if(!$SESSION->isLoggedIn()) {
    header ("Location: ".ROOT);
    die();
}


