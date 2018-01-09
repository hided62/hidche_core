<?
require_once('_common.php');

if(file_exists(ROOT.W.D_SETTING.W.SET.PHP)) ErrorToScreen('이미 설치되어 있습니다. 재설치하려면 설정 파일을 지우세요.');
if(fileperms(ROOT.W.D_SETTING.W) != 040707 && fileperms(ROOT.W.D_SETTING.W) != 040777) ErrorToScreen('설정 디렉토리 권한을 707 또는 777로 설정해주세요.');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>설치 시작</title>
    </head>

    <body>
        <form method="post" action="install1Post.php">
            DB정보<br>
            호스트 <input type="text" name="dbHost" value="localhost"><br>
            아이디 <input type="text" name="dbId"><br>
            비　번 <input type="password" name="dbPw"><br>
            디　비 <input type="text" name="dbName"><br>
            Mail정보<br>
            호스트 <input type="text" name="mailHost" value="smtp.gmail.com"><br>
            포  트 <input type="text" name="mailPort" value="465"><br>
            아이디 <input type="text" name="mailId"><br>
            비  번 <input type="password" name="mailPw"><br>
            메  일 <input type="text" name="mailAddr" value="@gmail.com"><br>
            <input type="submit" value="설치시도">
        </form>
    </body>
</html>
