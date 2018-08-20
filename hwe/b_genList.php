<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 7);
if ($type <= 0 || $type > 8) {
    $type = 7;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("암행부", 1);

$query = "select no,nation,level,con,turntime,belong from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$query = "select level,secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$nation = MYDB_fetch_array($result);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

if ($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

$sel = [];
$sel[$type] = "selected";

$templates = new \League\Plates\Engine('templates');

?>
<!DOCTYPE html>
<html>
<?php if ($con == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다!");
} ?>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 암행부</title>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>암 행 부<br><?=closeButton()?></td></tr>
    <tr><td><form name=form1 method=get>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1]??''?> value=1>자금</option>
            <option <?=$sel[2]??''?> value=2>군량</option>
            <option <?=$sel[3]??''?> value=3>도시</option>
            <option <?=$sel[4]??''?> value=4>병종</option>
            <option <?=$sel[5]??''?> value=5>병사</option>
            <option <?=$sel[6]??''?> value=6>삭제턴</option>
            <option <?=$sel[7]??''?> value=7>턴</option>
            <option <?=$sel[8]??''?> value=8>부대</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php
$troopName = [];
foreach($db->queryAllLists('SELECT troop, name FROM troop WHERE nation=%i', $me['nation']) as [$troopID, $tName]){
    $troopName[$troopID] = $tName;
}

$orderSQL = '';
switch ($type) {
    case 1: $orderSQL = "order by gold desc"; break;
    case 2: $orderSQL = "order by rice desc"; break;
    case 3: $orderSQL = "order by city"; break;
    case 4: $orderSQL = "order by crewtype desc"; break;
    case 5: $orderSQL = "order by crew desc"; break;
    case 6: $orderSQL = "order by killturn"; break;
    case 7: $orderSQL = "order by turntime"; break;
    case 8: $orderSQL = "order by troop desc"; break;
}

$generals = $db->query('SELECT npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general WHERE nation = %i %l', $me['nation'], $orderSQL);

foreach ($generals as &$general) {
    $general['cityText'] = CityConst::byID($general['city'])->name;
    $general['troopText'] = $troopName[$general['troop']]??'-';

    if ($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif ($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }
    if ($lbonus > 0) {
        $lbonusText = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonusText = "";
    }
    $general['lbonus'] = $lbonus;
    $general['lbonusText'] = $lbonusText;

    if ($general['injury'] > 0) {
        $leader = intdiv($general['leader'] * (100 - $general['injury']), 100);
        $power = intdiv($general['power'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
        $leader = "<font color=red>{$leader}</font>{$lbonusText}";
        $power = "<font color=red>{$power}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leader = "{$general['leader']}{$lbonusText}";
        $power = "{$general['power']}";
        $intel = "{$general['intel']}";
    }

    $general['leaderText'] = $leader;
    $general['powerText'] = $power;
    $general['intelText'] = $intel;

    $general['expLevelText'] = getExpLevel($general['experience']);

    if ($general['npc'] >= 2) {
        $name = "<font color=cyan>{$general['name']}</font>";
    } elseif ($general['npc'] == 1) {
        $name = "<font color=skyblue>{$general['name']}</font>";
    } else {
        $name =  $general['name'];
    }
    //TODO: npc 코드를 일원화
    $general['nameText'] = $name;

    switch ($general['mode']) {
        case 0: $mode = "×"; break;
        case 1: $mode = "○"; break;
        case 2: $mode = "◎"; break;
    }
    $general['modeText'] = $mode;
    $general['crewtypeText'] = GameUnitConst::byId($general['crewtype'])->name??'-';

    
    if ($general['npc'] < 2) {
        $turntext = [];
        $turn = getTurn($general, 1, 0);

        for ($i=0; $i < 5; $i++) {
            $turn[$i] = StringUtil::subStringForWidth($turn[$i], 0, 41);
            $k = $i+1;
            $turntext[] = "&nbsp;$k : $turn[$i]";
        }
        $general['turntext'] = join("<br>\n", $turntext);
    }
}
unset($general);

$genCnt = count($generals);
$totalGold = 0;
$totalRice = 0;
$crew90 = 0;
$gen90 = 0;
$crew80 = 0;
$gen80 = 0;
$crew60 = 0;
$gen60 = 0;
$crewTotal = 0;
//$genTotal = 0;
foreach($generals as $general){
    $totalGold += $general['gold'];
    $totalRice += $general['rice'];

    $crewTotal += $general['crew'];

    if($general['crew'] == 0){
        continue;
    }
    if($general['train'] >= 90 && $general['atmos'] >= 90){
        $crew90 += $general['crew'];
        $gen90 += 1;
    }

    if($general['train'] >= 80 && $general['atmos'] >= 80){
        $crew80 += $general['crew'];
        $gen80 += 1;
    }

    if($general['train'] >= 60 && $general['atmos'] >= 60){
        $crew60 += $general['crew'];
        $gen60 += 1;
    }
}
?>

<table style='width:1000px;margin:5px auto' class='tb_layout bg0'>
<thead>
<colgroup>
<col style="width:120px;"><col>
<col style="width:120px;"><col>
<col style="width:120px;"><col>
<col style="width:120px;"><col>
</colgroup>
</thead>
<tbody>
<tr>
<td class='bg1'>전체 금</td><td><?=number_format($totalGold)?></td>
<td class='bg1'>전체 쌀</td><td><?=number_format($totalRice)?></td>
<td class='bg1'>평균 금</td><td><?=number_format($totalGold/$genCnt, 2)?></td>
<td class='bg1'>평균 쌀</td><td><?=number_format($totalRice/$genCnt, 2)?></td>
</tr>
<tr>
<td class='bg1'>전체 병력/장수</td><td><?=number_format($crewTotal)?>/<?=number_format($genCnt)?></td>
<td class='bg1'>훈사 90 병력/장수</td><td><?=number_format($crew90)?>/<?=number_format($gen90)?></td>
<td class='bg1'>훈사 80 병력/장수</td><td><?=number_format($crew80)?>/<?=number_format($gen80)?></td>
<td class='bg1'>훈사 60 병력/장수</td><td><?=number_format($crew60)?>/<?=number_format($gen60)?></td>
</tr>
</tbody>
</table>

<?=$templates->render('generalList', ['generals'=>$generals])?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
