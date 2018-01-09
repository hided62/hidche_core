<?
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._Session.php');

$SESSION = new _Session();

if($SESSION->IsLoggedIn() == false) {
//    echo('<script>window.top.entrance.location.replace("'.ROOT.W.'indexLogin.php");</script>');
    echo('<script>window.top.entrance.location.replace("'.ROOT.W.'i_login/login.php");</script>');
    exit(1);
}

?>
