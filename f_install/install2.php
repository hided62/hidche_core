<?php
require_once('_common.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>설치 시작</title>
    </head>

    <body>
        <form method="post" action="install2Post.php">
            아이디 <input type="text" name="id" value="admin"><br>
            비　번 <input type="password" name="pw"><br>
            <input type="submit" value="운영자등록">
        </form>
    </body>
</html>
