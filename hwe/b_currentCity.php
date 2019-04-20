<?php
namespace sammo;

include "lib.php";
include "func.php";

$citylist = Util::getReq('citylist', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$userGrade = Session::getUserGrade();

$db = DB::db();
$connect=$db->get();

increaseRefresh("현재도시", 1);

$query = "select no,nation,level,city from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$myNation = $db->queryFirstRow('SELECT nation,level,spy FROM nation WHERE nation=%i', $me['nation']);

$templates = new \League\Plates\Engine('templates');

?>
<!DOCTYPE html>
<html>

<head>
<title><?=UniqueConst::$serverName?>: 도시정보</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../e_lib/select2/select2.full.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/currentCity.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2-bootstrap4.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<style>
#general_list tr td{
    text-align:center;
}

#general_list tr td:last-child{
    text-align:left;
    padding-left:1em;
}

.general_turn_text{
    font-size:x-small;
}
</style>
</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>도 시 정 보<br><?=backButton()?></td></tr>
</table>

<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td width=998>
            <form name=cityselect method=get>
                <div style='text-align:center;'>도시선택 :
                <select id="citySelector" name=citylist size=1 style='display:inline-block;min-width:400px;'>
<?php
if(!$citylist){
    $citylist = $me['city'];
}

// 재야일때는 현재 도시만
$valid = 0;
if($me['level'] == 0) {
    $query = "select city,name,nation from city where city='{$me['city']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($cityresult);
    echo "
                    <option value={$city['city']}";
    if($city['city'] == $citylist) { echo " selected"; $valid = 1; }
    echo ">【".StringUtil::padString($city['name'], 4, '_')."】";
    if($city['nation'] == 0) echo "공백지";
    elseif($me['nation'] == $city['nation']) echo "본국";
    else echo "타국";
    echo "</option>";
} else {
    // 아국 도시들 선택
    $query = "select city,name,nation from city where nation='{$me['nation']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        echo "
                        <option value={$city['city']}";
        if($city['city'] == $citylist) { echo " selected"; $valid = 1; }
        echo ">【".StringUtil::padString($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국";
        else echo "타국";
        echo "</option>";
    }

    // 아국 장수가 있는 타국 도시들 선택
    $query = "select distinct A.city,B.name,B.nation from general A,city B where A.city=B.city and A.nation='{$me['nation']}' and B.nation!='{$me['nation']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        echo "
                        <option value={$city['city']}";
        if($city['city'] == $citylist) { echo " selected"; $valid = 1; }
        echo ">【".StringUtil::padString($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국";
        else echo "타국";
        echo "</option>";
    }
}

if($myNation['level'] > 0) {
    // 첩보도시도 목록에 추가

    $rawSpy = $myNation['spy'];

    if($rawSpy == ''){
        $spyCities = [];
    }
    else if(strpos($rawSpy, '|') !== false || is_numeric($rawSpy)){
        //TODO: 0.8 버전 이후에는 삭제할 것. 이후 버전은 json으로 변경됨.
        $spyCities = array_map(function($val){
            $val = intval($val);
            return intdiv($val, 10);
        }, 'intval',explode('|', $myNation['spy']));
    }
    else{
        $spyCities = array_keys(Json::decode($rawSpy));
    }


    if($spyCities){
        foreach ($db->query('SELECT city,name,nation FROM city WHERE city in %li', $spyCities) as $city) {
            echo "<option value={$city['city']}";
            if($city['city'] == $citylist) { echo " selected"; $valid = 1; }
            echo ">【".StringUtil::padString($city['name'], 4, '_')."】";
            if($city['nation'] == 0) echo "공백지";
            elseif($me['nation'] == $city['nation']) echo "본국";
            else echo "타국";
            echo "</option>";
        }
    }

}

echo "
                </select></div>
                <p align=center>명령 화면에서 도시를 클릭하셔도 됩니다.</p>
            </form>
        </td>
    </tr>
</table>
<br>";

unset($city);

// 첩보된 도시까지만 허용
if($valid == 0 && $userGrade < 5) {
    $citylist = $me['city'];
}


$city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $citylist);
$cityNation = getNationStaticInfo($city['nation']);

//태수, 군사, 종사
$gen1 = $db->queryFirstRow('SELECT `name`, npc FROM general WHERE `no`=%i', $city['gen1']);
$gen2 = $db->queryFirstRow('SELECT `name`, npc FROM general WHERE `no`=%i', $city['gen2']);
$gen3 = $db->queryFirstRow('SELECT `name`, npc FROM general WHERE `no`=%i', $city['gen3']);

if($city['trade'] === null) {
    $city['trade'] = "- ";
}

$query = "select npc,mode,no,picture,imgsvr,name,injury,leader,power,intel,level,nation,crewtype,crew,train,atmos from general where city='{$city['city']}' order by dedication desc";    // 장수 목록
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

$generals = $db->query(
    'SELECT npc,mode,no,picture,imgsvr,name,injury,leader,power,intel,level,nation,crewtype,crew,train,atmos from general where city=%i order by name',
    $city['city']
);

$generalTurnList = [];

foreach($db->queryAllLists(
    'SELECT general_id, turn_idx, action, arg FROM general_turn WHERE general_id IN %li AND turn_idx < 5 ORDER BY general_id ASC, turn_idx ASC', 
    array_column($generals, 'no')
    ) as [$generalID, $turnIdx, $action, $arg]
){
    if(!key_exists($generalID, $generalTurnList)){
        $generalTurnList[$generalID] = [];
    }
    $generalTurnList[$generalID][$turnIdx] = [$action, Json::decode($arg)];
}

$nationname = [];
$nationlevel = [];
foreach(getAllNationStaticInfo() as $nation){
    $nationname[$nation['nation']] = $nation['name'];
    $nationlevel[$nation['nation']] = $nation['level'];
}

//도시명	오	적군	0/0(0)	병장(총)	0/0(4)	90병장	0/0	60병장	0/0	수비○	0/0
$generalsFormat = [];


for($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);

    $nationInfo = getNationStaticInfo($general['nation']);

    if($general['nation'] != 0 && $general['nation'] == $myNation['nation']){
        $ourGeneral = true;
    }
    else{
        $ourGeneral = false;
    }

    if($userGrade == 6){
        $ourGeneral = true;
    }

    $isNPC = $general['npc']>1;
    $wounded = $general['injury'];
    

    $name = $general['name'];
    $nameText = formatName($name, $general['npc']);

    $leadership = $general['leader'];
    $power = $general['power'];
    $intel = $general['intel'];

    $leadershipText = formatWounded($leadership, $general['injury']);
    $powerText = formatWounded($power, $general['injury']);
    $intelText = formatWounded($intel, $general['injury']);

    $level = $general['level'];
    $levelText = getLevel($general['level']);

    if($general['level'] == 12) {
        $leadershipBonus = $nationInfo['level'] * 2;
    } elseif($general['level'] >= 5) {
        $leadershipBonus = $nationInfo['level'];
    } else {
        $leadershipBonus = 0;
    }
    $leadershipBonusText = formatLeadershipBonus($leadershipBonus);

    if($ourGeneral){
        $defenceMode = $general['mode'];
        $defenceModeText = formatDefenceMode($defenceMode);
        $crewType = $general['crewtype'];
        $crewTypeText = GameUnitConst::byId($crewType)->name;
        $crew = $general['crew'];
        $train = $general['train'];
        $atmos = $general['atmos'];
    }
    else{
        $defenceMode = 0;
        $defenceModeText = '';
        $crewType = 0;
        $crewTypeText = '';
        $crew = $general['crew'];
        $train = -1;
        $atmos = -1;
    }

    $nation = $general['nation'];
    $nationName = $nationInfo['name'];

    if($ourGeneral && !$isNPC){
        $turnText = [];
        $generalObj = new General($general, null, null, null, false);
        $turnBrief = getGeneralTurnBrief($generalObj, $generalTurnList[$generalObj->getID()]);
        foreach($turnBrief as $turnRawIdx=>$turn){
            $turnIdx = $turnRawIdx+1;
            $turnText[] = "{$turnIdx} : $turn";
        }
        $turnText = join('<br>', $turnText);
    }
    else{
        $turnText = '';
    }
    
    $generalsFormat[] = [
        'ourGeneral'=>$ourGeneral,
        'isNPC'=>$isNPC,
        'wounded'=>$wounded,
        'name'=>$name,
        'nameText'=>$nameText,
        'leadership'=>$leadership,
        'leadershipText'=>$leadershipText,
        'leadershipBonus'=>$leadershipBonus,
        'leadershipBonusText'=>$leadershipBonusText,
        'level'=>$level,
        'levelText'=>$levelText,
        'power'=>$power,
        'powerText'=>$powerText,
        'intel'=>$intel,
        'intelText'=>$intelText,
        'defenceMode'=>$defenceMode,
        'defenceModeText'=>$defenceModeText,
        'crewType'=>$crewType,
        'crewTypeText'=>$crewTypeText,
        'crew'=>$crew,
        'train'=>$train,
        'atmos'=>$atmos,
        'nation'=>$nation,
        'nationName'=>$nationName,
        'turnText'=>$turnText
    ];
}

$generalsName = array_map(function($gen){return $gen['name'];}, $generalsFormat);

$enemyCrew = 0;
$enemyCnt = 0;
$enemyArmedCnt = 0;
$crew90 = 0;
$gen90 = 0;
$crew80 = 0;
$gen80 = 0;
$crew60 = 0;
$gen60 = 0;

$crewDef = 0;
$genDef = 0 ;

$crewTotal = 0;
$armedGenTotal = 0;
$genTotal = 0;


foreach($generalsFormat as $general){
    if($general['nation'] == 0){
        continue;
    }
    if($general['nation'] != $myNation['nation']){
        $enemyCnt += 1;
        $enemyCrew += $general['crew'];
        if($general['crew'] > 0){
            $enemyArmedCnt += 1;
        }
        continue;
    }

    $crewTotal += $general['crew'];
    $genTotal += 1;

    if($general['crew'] == 0){
        continue;
    }
    $armedGenTotal += 1;

    if($general['train'] >= 90 && $general['atmos'] >= 90){
        $crew90 += $general['crew'];
        $gen90 += 1;
    }

    $chkDef = false;
    if($general['train'] >= 80 && $general['atmos'] >= 80){
        $crew80 += $general['crew'];
        $gen80 += 1;
        if($general['defenceMode'] == 2){
            $crewDef += $general['crew'];
            $genDef += 1;
            $chkDef = true;
        }
    }

    if($general['train'] >= 60 && $general['atmos'] >= 60){
        $crew60 += $general['crew'];
        $gen60 += 1;

        if($general['defenceMode'] == 1 && !$chkDef){
            $crewDef += $general['crew'];
            $genDef += 1;
            $chkDef = true;
        }
    }

}

?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
</table>

<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=11 align=center style='color:<?=newColor($cityNation['color'])?>; background:<?=$cityNation['color']?>'>【 <?=CityConst::$regionMap[$city['region']]?> | <?=CityConst::$levelMap[$city['level']]?> 】 <?=$city['name']?></td>
        <td style='color:<?=newColor($cityNation['color'])?>; background:<?=$cityNation['color']?>' class='center'><?=date('m-d H:i:s')?></td>
    </tr>
    <tr>
        <td align=center width=48 class=bg1>주민</td>
        <td align=center width=112><?=$city['pop']?>/<?=$city['pop2']?></td>
        <td align=center width=48 class=bg1>농업</td>
        <td align=center width=108><?=$city['agri']?>/<?=$city['agri2']?></td>
        <td align=center width=48 class=bg1>상업</td>
        <td align=center width=108><?=$city['comm']?>/<?=$city['comm2']?></td>
        <td align=center width=48 class=bg1>치안</td>
        <td align=center width=108><?=$city['secu']?>/<?=$city['secu2']?></td>
        <td align=center width=48 class=bg1>수비</td>
        <td align=center width=108><?=$city['def']?>/<?=$city['def2']?></td>
        <td align=center width=48 class=bg1>성벽</td>
        <td align=center width=108><?=$city['wall']?>/<?=$city['wall2']?></td>
    </tr>
    <tr>
        <td align=center class=bg1>민심</td>
        <td align=center><?=round($city['trust'], 1)?></td>
        <td align=center class=bg1>시세</td>
        <td align=center><?=$city['trade']?>%</td>
        <td align=center class=bg1>인구</td>
        <td align=center><?=round($city['pop']/$city['pop2']*100, 2)?>%</td>
        <td align=center class=bg1>태수</td>
        <td align=center><?=$gen1['name']??'-'?></td>
        <td align=center class=bg1>군사</td>
        <td align=center><?=$gen2['name']??'-'?></td>
        <td align=center class=bg1>종사</td>
        <td align=center><?=$gen3['name']??'-'?></td>
    </tr>
    <tr>
        <td align=center class=bg1>도시명</td>
        <td align=center><?=$city['name']?></td>
        <td align=center class=bg1>적군</td>
        <td align=center><?=number_format($enemyCrew)?>/<?=number_format($enemyArmedCnt)?>(<?=number_format($enemyCnt)?>)</td>
        <td align=center class=bg1>병장(총)</td>
        <td align=center><?=number_format($crewTotal)?>/<?=number_format($armedGenTotal)?>(<?=number_format($genTotal)?>)</td>
        <td align=center class=bg1>90병장</td>
        <td align=center><?=number_format($crew90)?>/<?=number_format($gen90)?></td>
        <td align=center class=bg1>60병장</td>
        <td align=center><?=number_format($crew60)?>/<?=number_format($gen60)?></td>
        <td align=center class=bg1>수비○</td>
        <td align=center><?=number_format($crewDef)?>/<?=number_format($genDef)?></td>
    </tr>
    <tr>
        <td align=center class=bg1>장수</td>
        <td colspan=11><?=join(', ', $generalsName)?></td>
    </tr>
</table>

<br>
<table align=center class='tb_layout bg0'>
<thead>
    <tr>
        <td width=64 align=center class=bg1>얼 굴</td>
        <td width=128 align=center class=bg1>이 름</td>
        <td width=48 align=center class=bg1>통솔</td>
        <td width=48 align=center class=bg1>무력</td>
        <td width=48 align=center class=bg1>지력</td>
        <td width=78 align=center class=bg1>관 직</td>
        <td width=28 align=center class=bg1>守</td>
        <td width=78 align=center class=bg1>병 종</td>
        <td width=78 align=center class=bg1>병 사</td>
        <td width=48 align=center class=bg1>훈련</td>
        <td width=48 align=center class=bg1>사기</td>
        <td width=280 align=center class=bg1>명 령</td>
    </tr></thead>
    <tbody class='bg0' id='general_list'>
<?php
foreach($generalsFormat as $general){
    echo $templates->render('cityGeneral', $general);
}
?>
</tbody>
</table>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

