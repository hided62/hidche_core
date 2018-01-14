<?php
require('_common.php');
require(ROOT.W.F_CONFIG.W.SESSION.PHP);
?>

<!DOCTYPE html>
<html>

    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>서버목록</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href=<?=ROOT.W.F_CONFIG.W.CONFIG.CSS;?>>
        <link type="text/css" rel="stylesheet" href=<?=ROOT.W.F_CONFIG.W.APP.CSS;?>>

        <link type="text/css" rel="stylesheet" href=<?=ROOT.W.I.POPUP.W.STYLE;?>>
        <link type="text/css" rel="stylesheet" href=<?=ROOT.W.I.ENTRANCE.W.STYLE;?>>

        <!-- 액션 -->
        <script type="text/javascript" src=<?=ROOT.W.E_LIB.W.JQUERY;?>></script>
        <script type="text/javascript" src=<?=ROOT.W.E_LIB.W.MD5;?>></script>
        <script type="text/javascript" src=<?=ROOT.W.F_CONFIG.W.CONFIG.JS;?>></script>
        <script type="text/javascript" src=<?=ROOT.W.F_CONFIG.W.APP.JS;?>></script>
        <script type="text/javascript" src=<?=ROOT.W.F_FUNC.W.FUNC.JS;?>></script>

        <script type="text/javascript" src=<?=ROOT.W.I.POPUP.W.ACTION;?>></script>
        <script type="text/javascript" src=<?=ROOT.W.I.ENTRANCE.W.ACTION;?>></script>
        <script type="text/javascript">
$(document).ready(Entrance);

function Entrance() {
    ImportView("body", "<?=ROOT;?>"+W+I+POPUP+W+FRAME);
    ImportView("body", FRAME);

    Popup_Import();
    Popup_Init();
    Popup_Update();

    Entrance_Import();
    Entrance_Init();
    Entrance_Update();
}
        </script>
<?php require('../i_banner/analytics.php'); ?>
    </head>

    <body <?=BLOCKBODY;?>>
    </body>

</html>
