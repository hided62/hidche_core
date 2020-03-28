<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');
WebUtil::setHeaderNoCache();//FIXME: 이 파일에는 이럴 이유가 없다. javascript 기반으로 바꿔도 충분

if($sel == 0) $sel = 1;
?>
<!DOCTYPE html>
<html>

    <head>
    <meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
        <title>스크린샷</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<style type="text/css">

.leftFloat {
  float: left;
}

.rightFloat {
  float: right;
}

.clear {
  clear: both;
}
</style>

    </head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><font size=5 color=skyblue><b>스 크 린 샷</b></font></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td>
            ※ 크게 보고 싶은 스크린샷을 클릭하세요.
        </td>
    </tr>
    <tr>
        <td align=center>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_01.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=1')>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_02.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=2')>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_03.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=3')>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_04.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=4')>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_05.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=5')>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_06.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=6')>
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_07.jpg style=border-style:ridge;width:125px;height:75px; onclick=location.replace('screenshot.php?sel=7')>
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
            <img src=<?=ServConfig::$gameImagePath?>/screenshot_0<?=$sel?>.jpg style=border-style:ridge;>
        </td>
    </tr>
</table>
</body>
</html>
