<?php
namespace sammo;

include "lib.php";
include "func.php";

$userID = Session::getUserID();

if(!$userID) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

//회원 테이블에서 정보확인
$member = RootDB::db()->queryFirstRow("select no,name,picture,imgsvr,grade from MEMBER where no= %i", $userID);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$connect = dbConn();

?>
<!DOCTYPE html>
<html>
<head>
<title>장수생성</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel='stylesheet' href="css/common.css">
<script type="text/javascript" src=<?="../e_lib/jquery-3.2.1.min.js";?>></script>
<script type="text/javascript">

jQuery(function($){

var totalAbil = 150;
var $leader = $('#leader');
var $power = $('#power');
var $intel = $('#intel');

function abilityRand(){
    var leader = Math.random()*65 + 10;
    var power = Math.random()*65 + 10;
    var intel = Math.random()*65 + 10;
    var rate = leader + power + intel;

    leader = Math.floor(leader / rate * totalAbil);
    power = Math.floor(power / rate * totalAbil);
    intel = Math.floor(intel / rate * totalAbil);
    

    while(leader+power+intel < totalAbil){
        leader+=1;
    }
    
    if(leader > 75 || power > 75 || intel > 75 || leader < 10 || power < 10 || intel < 10){
        return abilityRand();
    }

    $leader.val(leader);
    $power.val(power);
    $intel.val(intel);
}


function abilityLeadpow(){
    var leader = Math.random() * 6;
    var power = Math.random() * 6;
    var intel = Math.random() * 1;
    var rate = leader + power + intel;

    leader = Math.floor(leader / rate * totalAbil);
    power = Math.floor(power / rate * totalAbil);
    intel = Math.floor(intel / rate * totalAbil);
    
    while(leader+power+intel < totalAbil){
        power+=1;
    }
    
    if(intel < 10){
        leader -= 10 - intel;
        intel = 10;
    }
    
    if(leader > 75){
        power += leader - 75;
        leader = 75;
    }
    
    if(power > 75){
        leader += power - 75;
        power = 75;
    }

    $leader.val(leader);
    $power.val(power);
    $intel.val(intel);
}

function abilityLeadint(){
    var leader = Math.random() * 6;
    var power = Math.random() * 1;
    var intel = Math.random() * 6;
    var rate = leader + power + intel;

    leader = Math.floor(leader / rate * totalAbil);
    power = Math.floor(power / rate * totalAbil);
    intel = Math.floor(intel / rate * totalAbil);

    while(leader+power+intel < totalAbil){
        intel+=1;
    }

    if(power < 10){
        leader -= 10 - power;
        power = 10;
    }
    
    if(leader > 75){
        intel += leader - 75;
        leader = 75;
    }
    
    if(intel > 75){
        leader += intel - 75;
        intel = 75;
    }

    $leader.val(leader);
    $power.val(power);
    $intel.val(intel);
}

function abilityPowint(){
    var leader = Math.random() * 1;
    var power = Math.random() * 6;
    var intel = Math.random() * 6;
    var rate = leader + power + intel;

    leader = Math.floor(leader / rate * totalAbil);
    power = Math.floor(power / rate * totalAbil);
    intel = Math.floor(intel / rate * totalAbil);

    while(leader+power+intel < totalAbil){
        intel+=1;
    }

    if(leader < 10){
        power -= 10 - leader;
        leader = 10;
    }
    
    if(power > 75){
        intel += power - 75;
        power = 75;
    }
    
    if(intel > 75){
        power += intel - 75;
        intel = 75;
    }

    $leader.val(leader);
    $power.val(power);
    $intel.val(intel);
}

window.abilityRand = abilityRand;
window.abilityLeadpow = abilityLeadpow;
window.abilityLeadint = abilityLeadint;
window.abilityPowint = abilityPowint;
});
</script>

</head>
<body>
    <table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
        <tr><td>장 수 생 성<br><?=backButton()?></td></tr>
    </table>
    <table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
        <tr><td align=center><?php info($connect, 0, 1); ?></td></tr>
    </table>
<?php
$query = "select img,maxgeneral from game limit 1";
$result = MYDB_query($query, $connect) or Error("join ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

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

<form name=form1 method=post action=join_post.php>
    <table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
        <tr>
            <td colspan=3 align=center id=bg1>장수 생성</td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>장수명</td>
            <td colspan=2>
                <input type=text name=name maxlength=6 size=12 style=color:white;background-color:black; value=<?=$member['name']?>>(6글자 이내)
            </td>
        </tr>
<?php
if($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "") {
    $imageTemp = GetImageURL($member['imgsvr']);
    echo "
        <tr>
            <td align=right id=bg1>전콘 사용 여부</td>
            <td width=64 height=64>
                <img src={$imageTemp}/{$member['picture']} border=0>
            </td>
            <td>
                <input type=checkbox name=pic value=1 checked>사용
            </td>
        </tr>
    ";
}
?>
        <tr>
            <td align=center colspan=3>
                계정관리에서 자신만을 표현할 수 있는 아이콘을 업로드 해보세요!
            </td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>성격</td>
            <td colspan=2>
                <select name=character size=1 maxlength=15 style=color:white;background-color:black;>
                    <option selected value=11>????</option>
<!--
                    <option value=10>은둔</option>
-->
                    <option value=9>안전</option>
                    <option value=8>유지</option>
                    <option value=7>재간</option>
                    <option value=6>출세</option>
                    <option value=5>할거</option>
                    <option value=4>정복</option>
                    <option value=3>패권</option>
                    <option value=2>의협</option>
                    <option value=1>대의</option>
                    <option value=0>왕좌</option>
                </select> ※보정은 도움말 참고
            </td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>통솔</td>
            <td colspan=2><input type="text" name="leader" id="leader" value="<?=$abil['leader'];?>"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>무력</td>
            <td colspan=2><input type="text" name="power" id="power" value="<?=$abil['power'];?>"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>지력</td>
            <td colspan=2><input type="text" name="intel" id="intel" value="<?=$abil['intel'];?>"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>능력치 조정</td>
            <td colspan=2>
                <input type=button value=랜덤형 onclick=abilityRand()>
                <input type=button value=통솔무력형 onclick=abilityLeadpow()>
                <input type=button value=통솔지력형 onclick=abilityLeadint()>
                <input type=button value=무력지력형 onclick=abilityPowint()>
            </td>
        </tr>
        <tr>
            <td align=center colspan=3>
                <font color=orange>모든 능력치는 ( 10 <= 능력치 <= 75 ) 사이로 잡으셔야 합니다.<br>
                그 외의 능력치는 가입되지 않습니다.</font>
            </td>
        </tr>
        <tr>
            <td align=center colspan=3>
                능력치의 총합은 150입니다. 가입후 0~10의 능력치 보너스를 받게 됩니다.<br>
                임의의 도시에서 재야로 시작하며 건국과 임관은 게임 내에서 실행합니다.
            </td>
        </tr>
        <tr>
            <td width=498 align=right><input type=submit name=join value=장수생성></td>
            <td colspan=2><input type=reset name=reset value=다시입력></td>
        </tr>
    </table>
</form>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
