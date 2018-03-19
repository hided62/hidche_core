<?php
require_once('_common.php');

$images = IMAGES;

if($sel == 0) $sel = 1;
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>스크린샷</title>

<style type="text/css">
<!--

body { color:white; background-color:black; border-width:1; border-color:gray; }
table { font-family:'맑은 고딕'; line-height:110%; }
font { font-family:'맑은 고딕'; line-height:110%; }
#bg0 { background-image:url(<?=$images;?>/back_walnut.jpg); }
#bg1 { background-image:url(<?=$images;?>/back_blue.jpg); }
#bg2 { background-image:url(<?=$images;?>/back_green.jpg); }

.leftFloat {
  float: left;
}

.rightFloat {
  float: right;
}

.clear {
  clear: both;
}

-->
</style>

    </head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><font size=5 color=skyblue><b>스 크 린 샷</b></font></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td>
            ※ 크게 보고 싶은 스크린샷을 클릭하세요.
        </td>
    </tr>
    <tr>
        <td align=center>
            <img src=<?=$images;?>/screenshot_01.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=1')>
            <img src=<?=$images;?>/screenshot_02.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=2')>
            <img src=<?=$images;?>/screenshot_03.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=3')>
            <img src=<?=$images;?>/screenshot_04.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=4')>
            <img src=<?=$images;?>/screenshot_05.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=5')>
            <img src=<?=$images;?>/screenshot_06.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=6')>
            <img src=<?=$images;?>/screenshot_07.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=7')>
        </td>
    </tr>
    <tr>
        <td align=center>
            <font size=6 color=orange><b>
<?php
switch($sel) {
case 1: echo "화 려 한 &nbsp; 중 원 지 도"; break;
case 2: echo "명 장 들 의 &nbsp; 자 랑 터"; break;
case 3: echo "일 기 토 대 회"; break;
case 4: echo "베 팅 기 능"; break;
case 5: echo "다 양 한 &nbsp; 관 직"; break;
case 6: echo "직 관 적 인 &nbsp; 로 그"; break;
case 7: echo "독 특 한 &nbsp; 커 뮤 니 티"; break;
}
?>
            </b></font>
        </td>
    </tr>
    <tr>
        <td align=center>
            <img src=<?=$images;?>/screenshot_0<?=$sel;?>.jpg style=border-style:ridge;>
        </td>
    </tr>
</table>
</body>
</html>
