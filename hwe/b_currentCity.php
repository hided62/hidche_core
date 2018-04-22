<?php
namespace sammo;

include "lib.php";
include "func.php";

$citylist = Util::getReq('citylist', 'int');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("현재도시", 1);

$query = "select no,nation,level,city from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select nation,level,spy from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$myNation = MYDB_fetch_array($result);

$templates = new \League\Plates\Engine('templates');

?>
<!DOCTYPE html>
<html>

<head>
<title>도시정보</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link href="../d_shared/common.css" rel="stylesheet">
<link href="css/common.css" rel="stylesheet">
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
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; class=bg0>
    <tr><td>도 시 정 보<br><?=backButton()?></td></tr>
</table>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; class=bg0>
    <tr>
        <td width=998>
            <form name=cityselect method=get>도시선택 :
                <select name=citylist size=1 style=color:white;background-color:black;width:798px;>
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
    echo ">==================================================【".StringUtil::padString($city['name'], 4, '_')."】";
    if($city['nation'] == 0) echo "공백지";
    elseif($me['nation'] == $city['nation']) echo "본국==";
    else echo "타국==";
    echo "============================================</option>";
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
        echo ">==================================================【".StringUtil::padString($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국==";
        else echo "타국==";
        echo "============================================</option>";
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
        echo ">==================================================【".StringUtil::padString($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국==";
        else echo "타국==";
        echo "============================================</option>";
    }
}

if($myNation['level'] > 0) {
    // 첩보도시도 목록에 추가
    $where = 'city=0';
    $cities = array_map('intval',explode("|", $myNation['spy']));
    foreach($cities as $citySpy) {
        $city = intdiv($citySpy, 10);
        $where .= " or city='{$city}'";
    }

    $query = "select city,name,nation from city where {$where}";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        echo "
                        <option value={$city['city']}";
        if($city['city'] == $citylist) { echo " selected"; $valid = 1; }
        echo ">==================================================【".StringUtil::padString($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국==";
        else echo "타국==";
        echo "============================================</option>";
    }

}

echo "
                </select>
                <input type=submit value='도 시 선 택'>
                <p align=center>명령 화면에서 도시를 클릭하셔도 됩니다.</p>
            </form>
        </td>
    </tr>
</table>
<br>";

unset($city);

// 첩보된 도시까지만 허용
if($valid == 0 && $session->userGrade < 5) {
    $citylist = $me['city'];
}


$city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $citylist);
$nation = getNationStaticInfo($city['nation']);

//태수, 군사, 시중
$gen1 = $db->queryFirstRow('SELECT `name`, npc FROM general WHERE `no`=%i', $city['gen1']);
$gen2 = $db->queryFirstRow('SELECT `name`, npc FROM general WHERE `no`=%i', $city['gen2']);
$gen3 = $db->queryFirstRow('SELECT `name`, npc FROM general WHERE `no`=%i', $city['gen3']);

if($city['trade'] == 0) {
    $city['trade'] = "- ";
}
?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; class=bg0>
    <tr><td><?=backButton()?></td></tr>
</table>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; class=bg2>
    <tr>
        <td colspan=12 align=center style=color:"<?=newColor($nation['color'])?>"; bgcolor=<?=$nation['color']?>>【 <?=CityConst::$regionMap[$city['region']]?> | <?=CityConst::$levelMap[$city['level']]?> 】 <?=$city['name']?></td>
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
        <td align=center><?=$city['rate']?></td>
        <td align=center class=bg1>시세</td>
        <td align=center><?=$city['trade']?>%</td>
        <td align=center class=bg1>인구</td>
        <td align=center><?=round($city['pop']/$city['pop2']*100, 2)?>%</td>
        <td align=center class=bg1>태수</td>
        <td align=center><?=$gen1['name']??'-'?></td>
        <td align=center class=bg1>군사</td>
        <td align=center><?=$gen2['name']??'-'?></td>
        <td align=center class=bg1>시중</td>
        <td align=center><?=$gen3['name']??'-'?></td>
    </tr>
    <tr>
        <td align=center class=bg1>장수</td>
        <td colspan=11>
<?php
    $query = "select name, npc from general where city='{$city['city']}' and nation='{$city['nation']}'";    // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);
    if($gencount == 0) echo "-";
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($genresult);
        echo "{$general['name']}, ";
    }
?>
        </td>
    </tr>
</table>


<br>
<table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; class=bg0>
<thead>
    <tr>
        <td width=64 align=center class=bg1>얼 굴</td>
        <td width=98 align=center class=bg1>이 름</td>
        <td width=48 align=center class=bg1>통솔</td>
        <td width=48 align=center class=bg1>무력</td>
        <td width=48 align=center class=bg1>지력</td>
        <td width=78 align=center class=bg1>관 직</td>
        <td width=28 align=center class=bg1>守</td>
        <td width=78 align=center class=bg1>병 종</td>
        <td width=78 align=center class=bg1>병 사</td>
        <td width=48 align=center class=bg1>훈련</td>
        <td width=48 align=center class=bg1>사기</td>
        <td width=310 align=center class=bg1>명 령</td>
    </tr></thead><tbody class='bg0' id='general_list'>

<?php
$query = "select npc,mode,no,picture,imgsvr,name,injury,leader,power,intel,level,nation,crewtype,crew,train,atmos,term,turn0,turn1,turn2,turn3,turn4,turn5 from general where city='{$city['city']}' order by dedication desc";    // 장수 목록
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

$nationname = [];
$nationlevel = [];
foreach(getAllNationStaticInfo() as $nation){
    $nationname[$nation['nation']] = $nation['name'];
    $nationlevel[$nation['nation']] = $nation['level'];
}


for($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);

    $nationInfo = getNationStaticInfo($general['nation']);

    if($general['nation'] != 0 && $general['nation'] == $myNation['nation']){
        $ourGeneral = true;
    }
    else{
        $ourGeneral = false;
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
        foreach(getTurn($general, 1) as $turnRawIdx=>$turn){
            $turnIdx = $turnRawIdx+1;
            $turnText[] = "{$turnIdx} : $turn";
        }
        $turnText = join('<br>', $turnText);
    }
    else{
        $turnText = '';
    }
    
    echo $templates->render('cityGeneral', [
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
    ]);
}
?>
</tbody>
</table>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; class=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

