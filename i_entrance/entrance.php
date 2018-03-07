<?php
require_once('_common.php');
?>

<!DOCTYPE html>
<html>

    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>서버목록</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href='../f_config/config.css'>
        <link type="text/css" rel="stylesheet" href='../f_config/app.css'>

        <link type="text/css" rel="stylesheet" href='../i_popup/Style.css'>
        <link type="text/css" rel="stylesheet" href='../i_entrance/Style.css'>

        <!-- 액션 -->
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src='../e_lib/md5-min.js'></script>
        <script type="text/javascript" src='../f_config/config.js'></script>
        <script type="text/javascript" src='../f_config/app.js'></script>
        <script type="text/javascript" src='../f_func/func.js'></script>

        <script type="text/javascript" src='../i_popup/Action.js'></script>
        <script type="text/javascript" src='../i_entrance/Action.js'></script>
        <script type="text/javascript">
$(document).ready(Entrance);

function Entrance() {
    ImportView("body", "../i_popup/Frame.php");
    ImportView("body", FRAME);

    Popup_Import();
    Popup_Init();
    Popup_Update();

    Entrance_Import();
    Entrance_Init();
    Entrance_Update();
}
        </script>
    </head>

    <body>
    </body>

</html>
