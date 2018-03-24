<?php
namespace sammo;

require_once('_common.php');

$SESSION = new Session();

if(!$SESSION->isLoggedIn()) {
    header ("Location: ".ROOT);
    die();
}


