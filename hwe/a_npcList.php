<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 1);

if($type <= 0 || $type > 8){
    $type = 1;
}

$db = DB::db();

increaseRefresh("빙의일람", 2);

$sel = [];
$sel[$type] = "selected";

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 빙의일람</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>빙 의 일 람<br><?=closeButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1]??''?> value=1>이름</option>
            <option <?=$sel[2]??''?> value=2>국가</option>
            <option <?=$sel[3]??''?> value=3>종능</option>
            <option <?=$sel[4]??''?> value=4>통솔</option>
            <option <?=$sel[5]??''?> value=5>무력</option>
            <option <?=$sel[6]??''?> value=6>지력</option>
            <option <?=$sel[7]??''?> value=7>명성</option>
            <option <?=$sel[8]??''?> value=8>계급</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php
$nationName = [];
$nationName[0] = "-";
foreach (getAllNationStaticInfo() as $nation) {
    $nationName[$nation['nation']] = $nation['name'];
}


$generalList = $db->query('SELECT npc,nation,name,owner_name,special,special2,personal,leadership,strength,intel,leadership+strength+intel as sum,explevel,experience,dedication from general where npc=1');
$sortType = [
    1 => ['name', true],
    2 => ['nation', true],
    3 => ['sum', false],
    4 => ['leadership', false],
    5 => ['strength', false],
    6 => ['intel', false],
    7 => ['experience', false],
    8 => ['dedication', false],
];

[$sortKey, $isAsc] = $sortType[$type];

if($isAsc){
    usort($generalList, function($lhs, $rhs)use($sortKey){
        return $lhs[$sortKey] <=> $rhs[$sortKey];
    });
}
else{
    usort($generalList, function($lhs, $rhs)use($sortKey){
        return $rhs[$sortKey] <=> $lhs[$sortKey];
    });
}

?>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td width=102  align=center id=bg1>희생된 장수</td>
        <td width=102  align=center id=bg1>악령 이름</td>
        <td width=68  align=center id=bg1>레벨</td>
        <td width=118 align=center id=bg1>국가</td>
        <td width=68  align=center id=bg1>성격</td>
        <td width=88  align=center id=bg1>특기</td>
        <td width=68  align=center id=bg1>종능</td>
        <td width=68  align=center id=bg1>통솔</td>
        <td width=68  align=center id=bg1>무력</td>
        <td width=68  align=center id=bg1>지력</td>
        <td width=78  align=center id=bg1>명성</td>
        <td width=78  align=center id=bg1>계급</td>
    </tr>
<?php foreach($generalList as $general): ?>
    <tr>
        <td align=center><?=getColoredName($general['name'], $general['npc'])?></td>
        <td align=center><?=$general['owner_name']?></td>
        <td align=center>Lv <?=$general['explevel']?></td>
        <td align=center><?=$nationName[$general['nation']]?></td>
        <td align=center><?=displayCharInfo($general['personal'])?></td>
        <td align=center><?=displaySpecialDomesticInfo($general['special'])?> / <?=displaySpecialWarInfo($general['special2'])?></td>
        <td align=center><?=$general['sum']?></td>
        <td align=center><?=$general['leadership']?></td>
        <td align=center><?=$general['strength']?></td>
        <td align=center><?=$general['intel']?></td>
        <td align=center><?=$general['experience']?></td>
        <td align=center><?=$general['dedication']?></td>
    </tr>
<?php endforeach; ?>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>

</html>
