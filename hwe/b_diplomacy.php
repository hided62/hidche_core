<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("중원정보", 1);

$mapTheme = $gameStor->map_theme??'che';

$query = "select no,nation from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select nation,color,name,power,gennum from nation where level>0 order by power desc";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nationcount = MYDB_num_rows($result);
$nationnum = [];
$nationname = [];

$nationStr = "";
$powerStr = "";
$genStr = "";
$cityStr = "";
for($i=0; $i < $nationcount; $i++) {
    $nation = MYDB_fetch_array($result);

    $query = "select city from city where nation='{$nation['nation']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);


    $nationnum[] = $nation['nation'];
    $nationname[$nation['nation']] = $nation['name'];

    $nationStr .= "<font color=cyan>◆</font> <font style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</font><br>";
    $powerStr .= "{$nation['power']}<br>";
    $genStr .= "{$nation['gennum']}<br>";
    $cityStr .= "$citycount<br>";
}

$realConflict = [];
foreach ($db->queryAllLists('SELECT city, `name`, conflict FROM city WHERE conflict!=%s', '{}') as [
    $cityID, 
    $cityName, 
    $rawConflict
])
{
    $conflict = Json::decode($rawConflict);
    if (count($conflict)<2) {
        continue;
    }

    $sum = array_sum($conflict);

    foreach ($conflict as $nationID=>$killnum) {
        $conflict[$nationID] = ['killnum'=>$killnum];
        $conflict[$nationID]['percent'] = round(100*$killnum / $sum, 1);
        $conflict[$nationID]['name'] = $nationname[$nationID];
        $conflict[$nationID]['color'] = getNationStaticInfo($nationID)['color'];
    }

    $realConflict[] = [$cityID, $cityName, $conflict];
};


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 중원 정보</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('d_shared/base_map.js')?>
<?=WebUtil::printJS('js/map.js')?>
<script>
window.serverNick = '<?=DB::prefix()?>';
window.serverID = '<?=UniqueConst::$serverID?>';
$(function(){

    reloadWorldMap({
        neutralView:true,
        showMe:true,
        useCachedMap:true
    });

});
</script>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/map.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>중 원 정 보<br><?=backButton()?></td></tr>
</table>
<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=<?=$nationcount+1?> align=center bgcolor=blue>외 교 현 황</td></tr>
<?php
echo "
    <tr>
        <td align=center width=130 style=background-color:".GameConst::$basecolor2.";>&nbsp;</td>";

if($nationcount != 0) {
    $width = intdiv(888, $nationcount);
}

for($i=0; $i < $nationcount; $i++) {
    echo "
        <td align=center width={$width} style=background-color:".getNationStaticInfo($nationnum[$i])['color'].";color:".newColor(getNationStaticInfo($nationnum[$i])['color']).";>{$nationname[$nationnum[$i]]}</td>";
}
echo "
    </tr>";

$state = [];

for($i=0; $i < $nationcount; $i++) {
    $query = "select you,state from diplomacy where me='$nationnum[$i]'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount2 = MYDB_num_rows($dipresult);
    for($k=0; $k < $nationcount2; $k++) {
        $dip = MYDB_fetch_array($dipresult);
        $state[$dip['you']] = $dip['state'];
    }
    echo "
    <tr>
        <td align=center style=background-color:".getNationStaticInfo($nationnum[$i])['color'].";color:".newColor(getNationStaticInfo($nationnum[$i])['color']).";>{$nationname[$nationnum[$i]]}</td>";

    for($k=0; $k < $nationcount; $k++) {
        if($i == $k) {
            $str = "＼";
        } else {
            switch($state[$nationnum[$k]]) {
                case 0: $str = "<font color=red>★</font>"; break;
                case 1: $str = "<font color=magenta>▲</font>"; break;
                case 2:
                    if($nationnum[$i] == $me['nation'] || $nationnum[$k] == $me['nation'] || $session->userGrade >= 5) { $str = "ㆍ"; }
                    else { $str = "?"; }
//                    $str = "ㆍ";
                    break;
                case 3: $str = "<font color=cyan>○</font>"; break;
                case 4: $str = "<font color=cyan>○</font>"; break;
                case 5: $str = "<font color=cyan>◎</font>"; break;
                case 6: $str = "<font color=cyan>◎</font>"; break;
                case 7:
                    if($nationnum[$i] == $me['nation'] || $nationnum[$k] == $me['nation'] || $session->userGrade >= 5) { $str = "<font color=green>@</font>"; }
                    else { $str = "?"; }
//                    $str = "<font color=limegreen>@</font>";
                    break;
            }
        }

        if($nationnum[$i] == $me['nation'] || $nationnum[$k] == $me['nation']) { $backcolor = "style=background-color:".GameConst::$basecolor3.";"; }
        else { $backcolor = ""; }

        echo "
        <td align=center $backcolor>$str</td>";
    }
    echo "
    </tr>
";
}
?>
    <tr><td colspan=<?=$nationcount+1?> align=center>불가침 : <font color=limegreen>@</font>, 통합 : <font color=cyan>○</font>, 합병 : <font color=skyblue>◎</font>, 통상 : ㆍ, 선포 : <font color=magenta>▲</font>, 교전 : <font color=red>★</font></td></tr>
</table>

<?php if ($realConflict) : ?>

<br>
<table align='center' width=1000 class='tb_layout bg0'>
    <tr><td colspan=2 align=center bgcolor=magenta>분 쟁 현 황</td></tr>
    <?php foreach($realConflict as list($cityID, $cityName, $conflict)): ?>
    <tr>
        <td align=center width=48><?=$cityName?></td>
        <td style='width:948px;position:relative;'>
            <table class='tb_layout bg0' style='width:100%;'>
            <?php foreach($conflict as $item): ?>
                <tr>
                    <td 
                        width=130
                        align=right 
                        style='color:<?=newColor($item['color'])?>;background-color:<?=$item['color']?>;'
                    ><?=$item['name']?>&nbsp;</td>
                    <td width=48 align=right><?=(float)$item['percent']?> %&nbsp;</td>
                    <td width=*><div
                        style='display:inline-block;width:<?=$item['percent']?>%;background-color:<?=$item['color']?>;'
                    >&nbsp;</div></td>
                </tr>
            <?php endforeach; ?>
            </table>
    
        </td>
    </tr>
    <?php endforeach; ?>
    <tr><td colspan=2 height=5 id=bg1></td></tr>
</table>

<?php endif; ?> 

<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td colspan=5 align=center bgcolor=green><font size=3>중 원 지 도</font></td>
    </tr>
    <tr>
        <td width=698 height=420>
            <?=getMapHtml($mapTheme)?>
        </td>
        <td width=139 valign=top><div style='background-color:#cccccc;color:black;text-align:center'>국명</div><?=$nationStr?></td>
        <td width=70 valign=top style='text-align:center'><div style='background-color:#cccccc;color:black;'>국력</div><?=$powerStr?></td>
        <td width=43 valign=top style='text-align:center'><div style='background-color:#cccccc;color:black;'>장수</div><?=$genStr?></td>
        <td width=40 valign=top style='text-align:center'><div style='background-color:#cccccc;color:black;'>속령</div><?=$cityStr?></td>
    </tr>
</table>
<br>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
