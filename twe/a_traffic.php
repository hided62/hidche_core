<?php
include "lib.php";
include "func.php";
$connect = dbConn();
increaseRefresh($connect, "갱신정보", 2);

$query = "select year,month,refresh,maxrefresh,maxonline from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$game = MYDB_fetch_array($result);

$fp = @fopen("logs/_traffic.txt", "r");
@fseek($fp, -1000, SEEK_END);
$file = @fread($fp, 1000);
@fclose($fp);
$log = explode("\n",$file);
$str = "";
for($i=0; $i < $count; $i++) { $str .= ConvertLog($log[count($log)-2-$i], $skin)."<br>"; }
echo $str;

$curonline = getOnlineNum();
for($i=0; $i < 11; $i++) {
    $parse = explode("|", $log[count($log)-12+$i]);
    $date[$i]    = trim($parse[0]);
    $year[$i]    = trim($parse[1]);
    $month[$i]   = trim($parse[2]);
    $refresh[$i] = trim($parse[3]);
    $online[$i]  = trim($parse[4]);
}
if($game['maxrefresh'] == 0) { $game['maxrefresh'] = 1; }
if($game['maxrefresh'] < $game['refresh']) { $game['maxrefresh'] = $game['refresh']; }
if($game['maxonline'] == 0) { $game['maxonline'] = 1; }
if($game['maxonline'] < $curonline) { $game['maxonline'] = $curonline; }
?>
<html>
<head>
<title>트래픽정보</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>트 래 픽 정 보<br><?php closeButton(); ?></td></tr>
</table>
<br>
<table align=center width=1016 border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all;>
    <tr><td align=left>
        <table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
            <tr><td colspan=4 align=center id=bg2><font size=5>접 속 량</font></td></tr>
<?php
for($i=0; $i < 11; $i++) {
    $w = round($refresh[$i] / $game['maxrefresh'] * 100, 1);
    if($w >= 100) { $w -= 0.1; }
    if($refresh[$i] < 10 && $w < 3) { $w = 3; }
    elseif($refresh[$i] < 100 && $w < 6) { $w = 6; }
    elseif($refresh[$i] < 1000 && $w < 9) { $w = 9; }
    $w2 = round(100 - $w, 1);
    $color = getColor($w);
    $dt = substr($date[$i], 11, 5);
    echo "
            <tr height=30>
                <td width=100 align=center>{$year[$i]}년 {$month[$i]}월</td>
                <td width=60 align=center id=bg2>{$dt}</td>
                <td width=2 align=center id=bg1></td>
                <td width=320 align=center>
                    <table align=center width=100% height=30 border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                        <tr>
                            <td width={$w}% bgcolor={$color} align=right>{$refresh[$i]}&nbsp;</td>
                            <td width={$w2}% id=bg0><font size=1>&nbsp;</font></td>
                        </tr>
                    </table>
                </td>
            </tr>
    ";
}
$w = round($game['refresh'] / $game['maxrefresh'] * 100, 1);
if($w >= 100) { $w -= 0.1; }
if($game['refresh'] < 10 && $w < 3) { $w = 3; }
elseif($game['refresh'] < 100 && $w < 6) { $w = 6; }
elseif($game['refresh'] < 1000 && $w < 9) { $w = 9; }
$w2 = round(100 - $w, 1);
$color = getColor($w);
$dt = date('H:i');
echo "
            <tr height=30>
                <td width=100 align=center>{$game['year']}년 {$game['month']}월</td>
                <td width=60 align=center id=bg2>{$dt}</td>
                <td width=2 align=center id=bg1></td>
                <td width=320 align=center>
                    <table align=center width=100% height=30 border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                        <tr>
                            <td width={$w}% bgcolor={$color} align=right>{$game['refresh']}&nbsp;</td>
                            <td width={$w2}% id=bg0><font size=1>&nbsp;</font></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td colspan=4 height=5 align=center id=bg1></td></tr>
            <tr>
                <td colspan=4 height=30 align=center id=bg0>최고기록: {$game['maxrefresh']}</td>
            </tr>
";
?>
        </table>
    </td>
    <td align=right>
        <table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
            <tr><td colspan=4 align=center id=bg2><font size=5>접 속 자</font></td></tr>
<?php
for($i=0; $i < 11; $i++) {
    $w = round($online[$i] / $game['maxonline'] * 100, 1);
    if($w >= 100) { $w -= 0.1; }
    if($online[$i] < 10 && $w < 3) { $w = 3; }
    elseif($online[$i] < 100 && $w < 6) { $w = 6; }
    elseif($online[$i] < 1000 && $w < 9) { $w = 9; }
    $w2 = round(100 - $w, 1);
    $color = getColor($w);
    $dt = substr($date[$i], 11, 5);
    echo "
            <tr height=30>
                <td width=100 align=center>{$year[$i]}년 {$month[$i]}월</td>
                <td width=60 align=center id=bg2>{$dt}</td>
                <td width=2 align=center id=bg1></td>
                <td width=320 align=center>
                    <table align=center width=100% height=30 border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                        <tr>
                            <td width={$w}% bgcolor={$color} align=right>{$online[$i]}&nbsp;</td>
                            <td width={$w2}% id=bg0><font size=1>&nbsp;</font></td>
                        </tr>
                    </table>
                </td>
            </tr>
    ";
}
$w = round($curonline / $game['maxonline'] * 100, 1);
if($w >= 100) { $w -= 0.1; }
if($curonline < 10 && $w < 3) { $w = 3; }
elseif($curonline < 100 && $w < 6) { $w = 6; }
elseif($curonline < 1000 && $w < 9) { $w = 9; }
$w2 = round(100 - $w, 1);
$color = getColor($w);
$dt = date('H:i');
echo "
            <tr height=30>
                <td width=100 align=center>{$game['year']}년 {$game['month']}월</td>
                <td width=60 align=center id=bg2>{$dt}</td>
                <td width=2 align=center id=bg1></td>
                <td width=320 align=center>
                    <table align=center width=100% height=30 border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                        <tr>
                            <td width={$w}% bgcolor={$color} align=right>{$curonline}&nbsp;</td>
                            <td width={$w2}% id=bg0><font size=1>&nbsp;</font></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td colspan=4 height=5 align=center id=bg1></td></tr>
            <tr>
                <td colspan=4 height=30 align=center id=bg0>최고기록: {$game['maxonline']}</td>
            </tr>
";
?>
        </table>
    </td></tr>
</table>
<br>
<table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td colspan=3 align=center id=bg2><font size=5>주 의 대 상 자 (순간과도갱신)</font></td></tr>
<?php
$query = "select sum(refresh) as refresh,sum(connect) as connect from general";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$user = MYDB_fetch_array($result);
$user['connect'] = round($user['connect'], 1);
$maxrefresh = $user['refresh'];

$w = round($maxrefresh / $maxrefresh * 100, 1);
$w2 = round(100 - $w, 1);
$color = getColor($w);
echo "
    <tr id=bg2>
        <td width=98  align=center>주의대상자</td>
        <td width=98  align=center>벌점(순간갱신)</td>
        <td width=798 align=center>전체 대비</td>
    </tr>
    <tr>
        <td align=center>접속자 총합</td>
        <td align=center>{$user['connect']}({$maxrefresh})</td>
        <td align=center>
            <table align=center width=100% height=100% border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                <tr>
                    <td width={$w}% bgcolor={$color}>&nbsp;</td>
                    <td width={$w2}%>&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
";

$query = "select name,refresh,connect from general order by refresh desc limit 0,5";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$count = MYDB_num_rows($result);
for($i=0; $i < $count; $i++) {
    $user = MYDB_fetch_array($result);

    $w = round($user['refresh'] / $maxrefresh * 100, 1);
    $w2 = round(100 - $w, 1);
    $color = getColor($w);
    echo "
    <tr>
        <td width=98  align=center>{$user['name']}</td>
        <td width=98  align=center>{$user['connect']}({$user['refresh']})</td>
        <td width=798 align=center>
            <table align=center width=100% height=100% border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                <tr>
                    <td width={$w}% bgcolor={$color}>&nbsp;</td>
                    <td width={$w2}%>&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    ";
}
?>
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php closeButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

<?php
function getColor($per) {
    $r = getHex($per);
    $b = getHex(100 - $per);
    $color = $r . "00" . $b;
    return $color;
}

function getHex($dec) {
    $hex = floor($dec * 255 / 100);
    $code = getHexCode(floor($hex / 16));
    $code .= getHexCode($hex % 16);
    return $code;
}

function getHexCode($hex) {
    switch($hex) {
    case  0: return "0";    case  1: return "1";    case  2: return "2";    case  3: return "3";
    case  4: return "4";    case  5: return "5";    case  6: return "6";    case  7: return "7";
    case  8: return "8";    case  9: return "9";    case 10: return "A";    case 11: return "B";
    case 12: return "C";    case 13: return "D";    case 14: return "E";    case 15: return "F";
    }
}

