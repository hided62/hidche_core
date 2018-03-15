<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Session.php');

$SESSION = new _Session();

if($SESSION->isLoggedIn() == false) {
//    echo('<script>window.top.entrance.location.replace("'.ROOT.'/indexLogin.php");</script>');
    header ("Location: ".ROOT.'/i_login/login.php');
    exit(1);
    //echo('<script>window.top.entrance.location.replace("'.ROOT.'/i_login/login.php");</script>');
    //exit(1);
}


