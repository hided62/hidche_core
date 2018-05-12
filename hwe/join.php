<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();

if (!$userID) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

//회원 테이블에서 정보확인
$member = RootDB::db()->queryFirstRow("select no,name,picture,imgsvr,grade from member where no= %i", $userID);

if (!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 장수생성</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printJS('../e_lib/jquery-3.2.1.min.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/join.js')?>

</head>
<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr><td>장 수 생 성<br><?=backButton()?></td></tr>
    </table>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr><td align=center><?=info(0)?></td></tr>
    </table>
<?php
$admin = $gameStor->getValues(['show_img_level','maxgeneral']);

$query = "select no from general where npc<2";
$result = MYDB_query($query, $connect) or Error("join ".MYDB_error($connect), "");
$gencount = MYDB_num_rows($result);

if ($gencount >= $admin['maxgeneral']) {
    echo "<script>alert('더 이상 등록할 수 없습니다.');</script>";
    echo "<script>history.go(-1);</script>";
    exit();
}
?>

<table align=center width=1000 class='tb_layout bg0'>
<tr><td align=center colspan=2 id=bg1>임관 권유 메세지</td></tr>
<?php
$query = "select name,scoutmsg,color from nation";
$nationresult = MYDB_query($query, $connect) or Error("join ".MYDB_error($connect), "");
$nationcount = MYDB_num_rows($nationresult);

for ($i=0; $i < $nationcount; $i++) {
    $nation = MYDB_fetch_array($nationresult);
    if ($nation['scoutmsg'] == "") {
        echo "
    <tr><td align=center width=98 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>{$nation['name']}</td><td width=898 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>-</td></tr>";
    } else {
        echo "
    <tr><td align=center width=98 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>{$nation['name']}</td><td width=898 style=color:".newColor($nation['color']).";background-color:{$nation['color']}>{$nation['scoutmsg']}</td></tr>";
    }
}
?>
</table>

<form name=form1 method=post action=join_post.php>
    <table align=center width=1000 class='tb_layout bg0'>
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
if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "") {
    $imageTemp = GetImageURL($member['imgsvr']);
    echo "
        <tr>
            <td align=right id=bg1>전콘 사용 여부</td>
            <td width=64 height=64>
                <img width='64' height='64' src='{$imageTemp}/{$member['picture']}' border='0'>
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
            <td colspan=2><input type="number" name="leader" id="leader" value="50"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>무력</td>
            <td colspan=2><input type="number" name="power" id="power" value="50"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>지력</td>
            <td colspan=2><input type="number" name="intel" id="intel" value="50"></td>
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
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
