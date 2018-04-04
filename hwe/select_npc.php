<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$rootDB = RootDB::db();

$userID = $session->userID;
//회원 테이블에서 정보확인
$member = $rootDB->queryFirstRow('select no,name,picture,grade from MEMBER where no=%i', $userID); MYDB_fetch_array($result);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$admin = $rootDB->queryFirstRow('select npcmode,maxgeneral,show_img_level from game limit 1');

if($admin['npcmode'] != 1) {
    header('Location:join.php');
    die();
}

$db = DB::db();
$connect=$db->get();

?>
<!DOCTYPE html>
<html>
<head>
<title>NPC선택</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body onLoad='changeGen()'>
    <table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
        <tr><td>장 수 선 택<br><?=backButton()?></td></tr>
    </table>
    <table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
        <tr><td align=center><?php info(0, 1); ?></td></tr>
    </table>
<?php


$query = "select no from general where npc<2";
$result = MYDB_query($query, $connect) or Error("join ".MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

if($gencount >= $admin['maxgeneral']) {
    echo "<script>alert('더 이상 등록할 수 없습니다.');</script>";
    echo "<script>history.go(-1);</script>";
    exit();
}
?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<tr><td align=center colspan=2 id=bg1>임관 권유 메세지</td></tr>
<?php
$query = "select name,scoutmsg,color from nation";
$nationresult = MYDB_query($query, $connect) or Error("join ".MYDB_error($connect),"");
$nationcount = MYDB_num_rows($nationresult);

for($i=0; $i < $nationcount; $i++) {
    $nation = MYDB_fetch_array($nationresult);
    if($nation['scoutmsg'] == "") {
        echo "
    <tr><td align=center width=98 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>{$nation['name']}</td><td width=898 style=color:{newColor({$nation['color']})};background-color:{$nation['color']}>-</td></tr>";
    } else {
        echo "
    <tr><td align=center width=98 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>{$nation['name']}</td><td width=898 style=color:{newColor({$nation['color']})};background-color:{$nation['color']}>{$nation['scoutmsg']}</td></tr>";
    }
}
?>
</table>

<form name=form1 method=post action=select_npc_post.php>
    <table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
        <tr>
            <td colspan=2 align=center id=bg1>장수 선택</td>
        </tr>
<?php
if($admin['show_img_level'] >= 3) {
?>
        <tr>
            <td width=498 align=right rowspan=2 height=64 id=bg1>장수</td>
            <td width=498><img src=<?=$image;?>/1001.jpg border=0 name=picture width=64 height=64></td>
        </tr>
<?php
}
?>
        <tr>
            <td align=left colspan=2>
                <select name=face size=1 style=color:white;background-color:black; value=1001 disabled>
<?php
$query  = "select no,name,leader,power,intel from general where npc=2";
$result = MYDB_query($query,$connect);
$count = MYDB_num_rows($result);

for($i=0; $i < $count; $i++) {
    $npc = MYDB_fetch_array($result);
    $call = "{$npc['leader']} / {$npc['power']} / {$npc['intel']}";
    echo "
        <option value={$npc['no']}>{$npc['name']} 【{$call}】</option>";
}
?>

                </select>
            </td>
        </tr>
        <tr>
            <td align=center colspan=2>
                컴퓨터가 조작중이던 NPC장수를 조종하게 됩니다.<br>
                80시간동안 휴식을 취하면 다시 컴퓨터가 조종하게 되고 장수의 소유권을 잃습니다.
            </td>
        </tr>
        <tr>
            <td align=center colspan=2><input type=button name=sel value=다른장수 onclick='changeGen()'><input type=submit name=join value=장수선택 onclick='return selectGen()'></td>
        </tr>
    </table>
</form>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
<script type="text/javascript">
function changeGen() {
    sel = Math.floor(Math.random() * <?=$count;?>);
    document.form1.face.selectedIndex = sel;
    num = document.form1.face.value;
    document.form1.picture.src="<?=$image;?>" + "/"+ num +".jpg";
}

function selectGen() {
    document.form1.face.disabled = false;
    return true;
}
</script>
</html>
