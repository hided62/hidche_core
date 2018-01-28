<?php
require_once('_common.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>게임 시스템 설치 시작</title>
    </head>

    <body>
        <form name=form1 action="install1.php" method=post>
            <input type=submit value="설치시작" onclick="return confirm('회원 목록가 삭제됩니다!')">
        </form>
    </body>
</html>

