<?php
$fp = fopen("logs/_db_bug.txt", "a");
fwrite($fp, $message."\r\n");
fclose($fp);
?>

<html>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>에러</title>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>
<body>
<br><br><br><br><br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td align=center style=background-color:225500;><font color=orange size=5>서 버 에 러</font></td></tr>
    <tr>
        <td>
            <font color=white size=3>두가지 중 한가지 이유일 수 있습니다.<br><br>
            1. 현재 서버가 처리중입니다. 몇초 후 아래 버튼을 눌러주세요.<br><br>
            2. 오랫동안 이 메세지가 뜰 경우는 서버에 에러가 발생하여 잠시 중단된 상태입니다.<br>
            &nbsp;&nbsp;&nbsp;운영자가 처리할 때까지 기다려주세요.</font>
        </td>
    </tr>
    <tr><td align=center><font color=white size=3>잠시 후 아래의 버튼을 눌러주세요.</font></td></tr>
    <tr><td align=center><input type=button value='몇초간 지난 후 눌러주세요' onclick=location.replace('index.php')></td></tr>
    <tr><td align=center><?=$message;?></td></tr>
</table>
</body>
</html>

