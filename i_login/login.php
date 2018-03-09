<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Session.php');

$SESSION = new _Session();
if($SESSION->IsLoggedIn()){
    header(ROOT.'/i_entrance/Frame.php');
    die();
}
?>

<!DOCTYPE html>
<html>

    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>로그인</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href='../f_config/config.css'>
        <link type="text/css" rel="stylesheet" href='../f_config/app.css'>

        <link type="text/css" rel="stylesheet" href='../i_popup/Style.css'>
        <link type="text/css" rel="stylesheet" href='../i_login/Style.css'>

        <!-- 액션 -->
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src='../e_lib/md5-min.js'></script>
        <script type="text/javascript" src='../f_config/config.js'></script>
        <script type="text/javascript" src='../f_config/app.js'></script>
        <script type="text/javascript" src='../f_func/func.js'></script>

        <script type="text/javascript" src='../i_popup/Action.js'></script>
        <script type="text/javascript" src='../i_login/Action.js'></script>
        <script type="text/javascript">
$(document).ready(Login);

function Login() {
    ImportView("body", "<?=ROOT;?>"+W+I+POPUP+W+FRAME);
    ImportView("body", FRAME);

    Popup_Import();
    Popup_Init();
    Popup_Update();

    Login_Import();
    Login_Init();
    Login_Update();
}
        </script>
    </head>

    <body style="background-image:url('<?=IMAGES.'/back7.jpg'?>');">
    </body>

</html>
