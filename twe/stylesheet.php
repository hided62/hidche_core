<?php
include "lib.php";

use utilphp\util as util;

$connect=dbConn();

$query = "select month from game";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);



$tmp_id = util::array_get($_SESSION['p_id'],0); 
//xxx:와 이게 뭐지
//TODO:p_id 관련 스킨 세팅 확인


$query = "select no,skin,con from general where user_id='$tmp_id'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['no'] == 0) { $me['skin'] = 4; }

if($me['skin'] == 2) {
    switch($admin['month']) {
    case 1: case 2: case 3: $me['skin'] = 3; break;
    case 4: case 5: case 6: $me['skin'] = 4; break;
    case 7: case 8: case 9: $me['skin'] = 5; break;
    case 10: case 11: case 12: $me['skin'] = 6; break;
    }
}
if($me['skin'] == 14) {
    $me['skin'] = $me['con'] % 11 + 3;
}

$color = "white";
$backcolor = "black";
$back = "background-image:url({$images}/back.jpg); background-position:center; background-repeat:repeat-y;";
if($me['skin'] == 0) {
    $back = "";
    $bg0 = "background-color:black;";   $bg1 = "background-color:black;";   $bg2 = "background-color:black;";
} elseif($me['skin'] == 1) {
    $bg0 = "background-image:url({$images}/back_walnut.jpg);";
    $bg1 = "background-image:url({$images}/back_green.jpg);";
    $bg2 = "background-image:url({$images}/back_blue.jpg);";
} elseif($me['skin'] == 3) {
    $bg0 = "background-color:#330033;";  $bg1 = "background-color:#ff69b4;";  $bg2 = "background-color:#483d8b;";
} elseif($me['skin'] == 4) {
    $bg0 = "background-color:#001717;";  $bg1 = "background-color:#225500;";  $bg2 = "background-color:#000044;";
} elseif($me['skin'] == 5) {
    $bg0 = "background-color:#220000;";  $bg1 = "background-color:#b8860b;";  $bg2 = "background-color:#8b4513;";
} elseif($me['skin'] == 6) {
    $bg0 = "background-color:#222222;";  $bg1 = "background-color:#666666;";  $bg2 = "background-color:#444444;";
} elseif($me['skin'] == 7) {
    $bg0 = "background-color:#220000;";  $bg1 = "background-color:#660000;";  $bg2 = "background-color:#440000;";
} elseif($me['skin'] == 8) {
    $bg0 = "background-color:#002200;";  $bg1 = "background-color:#006600;";  $bg2 = "background-color:#004400;";
} elseif($me['skin'] == 9) {
    $bg0 = "background-color:#000022;";  $bg1 = "background-color:#000066;";  $bg2 = "background-color:#000044;";
} elseif($me['skin'] == 10) {
    $bg0 = "background-color:#002222;";  $bg1 = "background-color:#006666;";  $bg2 = "background-color:#004444;";
} elseif($me['skin'] == 11) {
    $bg0 = "background-color:#220022;";  $bg1 = "background-color:#660066;";  $bg2 = "background-color:#440044;";
} elseif($me['skin'] == 12) {
    $bg0 = "background-color:#222200;";  $bg1 = "background-color:#666600;";  $bg2 = "background-color:#444400;";
} elseif($me['skin'] == 13) {
    $bg0 = "background-color:#222222;";  $bg1 = "background-color:#666666;";  $bg2 = "background-color:#444444;";
} elseif($me['skin'] == 15) {
    $color = "white";
    $backcolor = "ff6600";
    $back = "background-image:url({$images}/skin_sosi.jpg); background-position:center; background-repeat:repeat-y;";
    $bg0 = "background-color:#663300;";  $bg1 = "background-color:#0099ff;";  $bg2 = "background-color:#ff6600;";
} elseif($me['skin'] == 16) {
    $color = "white";
    $backcolor = "400040";
    $back = "background-image:url({$images}/skin_taeyeon.jpg); background-position:center; background-repeat:repeat-y;";
    $bg0 = "background-image:url({$images}/back_walnut.jpg);";
    $bg1 = "background-image:url({$images}/back_green.jpg);";
    $bg2 = "background-image:url({$images}/back_blue.jpg);";
//    $bg0 = "background-color:#400040;";  $bg1 = "background-color:#ee82ee;";  $bg2 = "background-color:#da70d6;";
} elseif($me['skin'] == 17) {
//    $bg0 = "background-color:#9c1c6b;";  $bg1 = "background-color:#ca278c;";  $bg2 = "background-color:#e47297;";
    $bg0 = "background-color:#ff88c4;";  $bg1 = "background-color:#cc66ff;";  $bg2 = "background-color:#f5a2ff;";
}

header("Content-type: text/css");

?>

body { color:<?=$color;?>; background-color:<?=$backcolor;?>; border-width:1; border-color:gray; <?=$back;?> }
table { font-family:'맑은 고딕'; line-height:110%; }
font { font-family:'맑은 고딕'; line-height:110%; }
input { font-family:'맑은 고딕'; line-height:110%; height:20px }
select { font-family:'굴림'; line-height:100%; }
#bg0 { <?=$bg0;?> }
#bg1 { <?=$bg1;?> }
#bg2 { <?=$bg2;?> }
