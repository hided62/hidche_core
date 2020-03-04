<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사

$commandtype = Util::getReq('commandtype', 'int');
$turn = Util::getReq('turn', 'array_int');

if(!$turn || $commandtype === null){
    header('location:commandlist.php', true, 303);
    die();
}

$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();
$connect=$db->get();

$count = count($turn);
for($i=0; $i < $count; $i++) {
    if($turn[$i] == 100 || $turn[$i] == 99 || $turn[$i] == 98) {
    } elseif($turn[$i] >= 0 || $turn[$i] <= 23) {
    } else {
        unset($turn);
        $turn[0] = 100;
        break;
    }
}

if($turn[0] == 100) {
    unset($turn);
    for($i=0; $i < 24; $i++) $turn[$i] = $i;
} elseif($turn[0] == 99) {
    unset($turn);
    for($i=0, $j=0; $i < 24; $i+=2, $j++) $turn[$j] = $i;
} elseif($turn[0] == 98) {
    unset($turn);
    for($i=1, $j=0; $i < 24; $i+=2, $j++) $turn[$j] = $i;
}

switch($commandtype) {
    case  0: command_Single($turn, 0); break; //휴식
    case  1: command_Single($turn, 1); break; //농업
    case  2: command_Single($turn, 2); break; //상업
    case  3: command_Single($turn, 3); break; //기술
    case  4: command_Single($turn, 4); break; //선정
    case  5: command_Single($turn, 5); break; //수비
    case  6: command_Single($turn, 6); break; //성벽
    case  7: command_Single($turn, 7); break; //정착 장려
    case  8: command_Single($turn, 8); break; //치안 강화
    case  9: command_Single($turn, 9); break; //자금 조달

    case 11: command_11(    $turn, 11, false); break; //징병
    case 12: command_11(    $turn, 12, true); break; //모병
    case 13: command_Single($turn, 13); break; //훈련
    case 14: command_Single($turn, 14); break; //사기진작
    //case 15: command_Single($turn, 0); break; //전투태세 
    case 16: command_16(    $turn, 16); break; //전쟁
    case 17: command_Single($turn, 17); break; //소집해제

    case 21: command_21(    $turn, 21); break; //이동
    case 22: command_22(    $turn, 22); break; //등용 //TODO:등용장 재 디자인
    case 23: command_23(    $turn, 23); break; //포상
    case 24: command_24(    $turn, 24); break; //몰수
    case 25: command_25(    $turn, 25); break; //임관
    case 26: command_Single($turn, 26); break; //집합
    case 27: command_27(    $turn, 27); break; //발령
    case 28: command_Single($turn, 28); break; //귀환
    case 29: command_Single($turn, 29); break; //인재탐색
    case 30: command_30(    $turn, 30); break; //강행
    
    case 31: command_31($turn, 31); break; //첩보
    case 32: command_32($turn, 32); break; //화계
    case 33: command_33($turn, 33); break; //탈취
    case 34: command_34($turn, 34); break; //파괴
    case 35: command_35($turn, 35); break; //선동

    case 38: command_Single($turn, 38); break; //내특 초기화
    case 39: command_Single($turn, 39); break; //전특 초기화

    case 41: command_Single($turn, 41); break; //단련
    case 42: command_Single($turn, 42); break; //견문
    case 43: command_43(    $turn, 43); break; //증여
    case 44: command_44(    $turn, 44); break; //헌납
    case 45: command_Single($turn, 45); break; //하야
    case 46: command_46(    $turn, 46); break; //건국
    case 47: command_Single($turn, 47); break; //방랑
    case 48: command_48(    $turn, 48); break; //장비구입
    case 49: command_49(    $turn, 49); break; //군량매매
    case 50: command_Single($turn, 50); break; //요양

    case 51: command_51($turn, 51); break; //항복권고
    case 52: command_52($turn, 52); break; //원조
    case 53: command_53($turn, 53); break; //통합제의
    case 54: command_54($turn, 54); break; //선양
    case 55: command_Single($turn, 55); break; //거병
    case 56: command_Single($turn, 56); break; //해산
    case 57: command_Single($turn, 57); break; //모반 시도
    case 58: command_58(    $turn, 58); break; //숙련 전환

    case 61: command_61($turn, 61); break; //불가침
    case 62: command_62($turn, 62); break; //선포
    case 63: command_63($turn, 63); break; //종전
    case 64: command_64($turn, 64); break; //파기
    case 65: command_65($turn, 65); break; //초토화
    case 66: command_66($turn, 66); break; //천도
    case 67: command_67($turn, 67); break; //증축
    case 68: command_68($turn, 68); break; //감축
    case 71: command_Chief($turn, 71); break; //필사즉생
    case 72: command_72($turn, 72); break; //백성동원
    case 73: command_73($turn, 73); break; //수몰
    case 74: command_74($turn, 74); break; //허보
    case 75: command_75($turn, 75); break; //피장파장
    case 76: command_Chief($turn, 76); break; //의병모집
    case 77: command_77($turn, 77); break; //이호경식
    case 78: command_78($turn, 78); break; //급습
    case 81: command_81($turn, 81); break; //국기변경
    case 99: command_99($turn); break; //수뇌부 휴식
    
    default:command_Single($turn, 0); break; //휴식
}

function starter($name, $type=0) {
?>
<!DOCTYPE html>
<html>
<head>
<title><?=$name?></title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
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
    var $target = $("form[name=form1] select[name=double]");
    console.log($target);
    reloadWorldMap({
        isDetailMap:false,
        clickableAll:true,
        neutralView:true,
        useCachedMap:true,
        selectCallback:function(city){
            $target.val(city.id);
            return false;
        }
    });
});
</script>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/main.css')?>
<?=WebUtil::printCSS('css/map.css')?>
</head>
<body class="img_back">
<table class="tb_layout bg0" style="width:1000px;margin:auto;">
    <tr><td class="bg1" align="center"><?=$name?></td></tr>
    <tr><td>
<?php
    if($type == 1) echo CoreBackButton();
    else echo backButton();
}

function ender($type=0) {
    if($type == 1) echo CoreBackButton();
    else echo backButton();
    echo banner();
    echo "
    </td></tr>
</table>
</body>
</html>
";
}

function command_99($turn) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $query = "select nation,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $command = EncodeCommand(0, 0, 0, 99);

        for($i=0; $i < count($turn); $i++) {
            $query = "update nation set l{$me['level']}turn{$turn[$i]}='{$command}' where nation='{$me['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }

    header('location:b_chiefcenter.php', true, 303);
}

function command_11($turn, $command, bool $is모병 = false) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $me = $db->queryFirstRow(
        'SELECT no,nation,level,personal,special2,level,city,crew,horse,injury,leader,crewtype,gold 
        from general where owner=%i',
        $userID
    );

    [$nationLevel, $tech] = $db->queryFirstList('SELECT level,tech FROM nation WHERE nation=%i', $me['nation']);
    if(!$nationLevel){
        $nationLevel = 0;
    }

    if(!$tech){
        $tech = 0;
    }

    $lbonus = setLeadershipBonus($me, $nationLevel);

    $ownCities = [];
    $ownRegions = [];
    [$year, $startyear] = $gameStor->getValuesAsArray(['year','startyear']);

    $relativeYear = $year - $startyear;

    foreach(DB::db()->query('SELECT city, region from city where nation = %i', $me['nation']) as $city){
        $ownCities[$city['city']] = 1;
        $ownRegions[$city['region']] = 1;
    }

    $leader = getGeneralLeadership($me, true, true, true);
    $fullLeader = getGeneralLeadership($me, false, true, true);
    $abil = getTechAbil($tech);

    $armTypes = [];

    foreach(GameUnitConst::allType() as $armType => $armName){
        $armTypeCrews = [];
        
        foreach(GameUnitConst::byType($armType) as $unit){
            $crewObj = new \stdClass;
            $crewObj->showDefault = 'true';
            

            $crewObj->id = $unit->id;
            
            if($unit->reqTech == 0){
                $crewObj->bgcolor = 'green';
            }
            else{
                $crewObj->bgcolor = 'limegreen';
            }

            if(!$unit->isValid($ownCities, $ownRegions, $relativeYear, $tech)){
                $crewObj->showDefault = 'false';
                $crewObj->bgcolor = 'red';
            }
    
            $crewObj->baseRice = $unit->rice * getTechCost($tech);
            $crewObj->baseCost = CharCost($unit->costWithTech($tech), $me['personal']);

            $armType = $unit->armType;
            if($me['special2'] == 50 && $armType == GameUnitConst::T_FOOTMAN){
                $crewObj->baseCost *= 0.9;
            }
            else if($me['special2'] == 51 && $armType == GameUnitConst::T_ARCHER){
                $crewObj->baseCost *= 0.9;
            }
            else if($me['special2'] == 52 && $armType == GameUnitConst::T_CAVALRY){
                $crewObj->baseCost *= 0.9;
            }
            else if($me['special2'] == 53 && $armType == GameUnitConst::T_WIZARD){
                $crewObj->baseCost *= 0.9;
            }
            else if($me['special2'] == 54 && $armType == GameUnitConst::T_SIEGE){
                $crewObj->baseCost *= 0.9;
            }
            else if($me['special2'] == 72) { 
                $crewObj->baseCost *= 0.5; 
            }
    
            $crewObj->name = $unit->name;
            $crewObj->attack = $unit->attack + $abil;
            $crewObj->defence = $unit->defence + $abil;
            $crewObj->speed = $unit->speed;
            $crewObj->avoid = $unit->avoid;
            if($gameStor->show_img_level < 2) { 
                $crewObj->img = ServConfig::$sharedIconPath."/default.jpg"; 
            }
            else{
                $crewObj->img = ServConfig::$gameImagePath."/weap".$unit->id.".png";
            }
            
            $crewObj->baseRiceShort = round($crewObj->baseRice, 1);
            $crewObj->baseCostShort = round($crewObj->baseCost, 1);
    
            $crewObj->info = join('<br>', $unit->info);


            $armTypeCrews[] = $crewObj;
        }
        $armTypes[] = [$armName, $armTypeCrews];
    }
    if($is모병){
        $commandName = '모병';
        starter("모병");
    }
    else{
        $commandName = '징병';
        starter("징병");
    }

    $templates = new \League\Plates\Engine('templates');
    echo $templates->render('recruitCrewForm', [
        'command'=>$command,
        'commandName'=>$commandName,
        'techLevelText'=>getTechCall($tech),
        'tech'=>$tech,
        'leader'=>$leader,
        'fullLeader'=>$fullLeader,
        'crewType'=>GameUnitConst::byId($me['crewtype'])->id,
        'crewTypeName'=>GameUnitConst::byId($me['crewtype'])->name,
        'crew'=>$me['crew'],
        'gold'=>$me['gold'],
        'turn'=>$turn,
        'armTypes'=>$armTypes,
    ]);


    ender();
}

function command_15($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("재편성");
    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    $me = $general['no'];

    $query = "select no,name from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
자신의 병사를 다른 장수에게 보냅니다.
<form name=form1 action=c_double.php method=post>
<select name=third size=1>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($me != $general['no']) {
            echo "
    <option value={$general['no']}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<input type=text name=double size=3 maxlength=3 style=text-align:right;color:white;background-color:black>00명
<input type=submit value=재편성>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_16($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("출병");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시를 향해 침공을 합니다.<br>
침공 경로에 적군의 도시가 있다면 전투를 벌입니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=출병>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_21($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("이동");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 이동합니다.<br>
인접 도시로만 이동이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=이동>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitiesBasedOnDistance($currentcity['city'], 1);

    ender();
}

function command_22($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("등용");

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
재야나 타국의 장수를 등용합니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
등용할 장수를 목록에서 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    $query = "select no,name,npc from general where nation='0' and npc<2 order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    if($gencount != 0) echo "    <option>【 재야 】</option>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
    }

    foreach(getAllNationStaticInfo() as $nation){
        echo "<option style='color:{$nation['color']}'>【 {$nation['name']} 】</option>";

        $query = "select no,name,npc from general where nation='{$nation['nation']}' and level!='12' and npc<2 order by npc,binary(name)";
        $genresult = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
            elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
            else                       { $style = ""; }
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }
    echo "
</select>
<input type=submit value=등용>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender();
}

function command_23($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("포상", 1);
    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level,gold,rice,npc from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
국고로 장수에게 자금이나 군량을 지급합니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*(금:{$general['gold']}, 쌀:{$general['rice']})</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}(금:{$general['gold']}, 쌀:{$general['rice']})</option>";
        }
    }

    echo "
</select>
<select name=fourth size=1 style=color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=12>1200</option>
    <option value=15>1500</option>
    <option value=20>2000</option>
    <option value=25>2500</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=포상>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender(1);
}

function command_24($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("몰수", 1);
    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level,gold,rice,npc from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
장수의 자금이나 군량을 몰수합니다.<br>
몰수한것은 국가재산으로 귀속됩니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=text-align:right;color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 5) {
            echo "<option value={$general['no']} {$style}>*{$general['name']}*(금:{$general['gold']}, 쌀:{$general['rice']})</option>";
        } else {
            echo "<option value={$general['no']} {$style}>{$general['name']}(금:{$general['gold']}, 쌀:{$general['rice']})</option>";
        }
    }

    echo "
</select>
<select name=fourth size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=12>1200</option>
    <option value=15>1500</option>
    <option value=20>2000</option>
    <option value=25>2500</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=몰수>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender(1);
}

function command_25($turn, $command) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $userID = Session::getUserID();

    $gameStor->cacheValues(['year','startyear','month','join_mode']);

    $onlyRandom = $gameStor->join_mode == 'onlyRandom';
    starter("임관");

    $query = "select no,nations from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("command_27 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    
    $joinedNations = Json::decode($me['nations']);

    $query = "select nation,name,color,scout,scoutmsg,sabotagelimit,gennum from nation order by gennum";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    
    $nationList = $db->query('SELECT nation,`name`,color,scout,scoutmsg,gennum FROM nation ORDER BY rand()');

    foreach($nationList as &$nation){
        if ($onlyRandom && TimeUtil::IsRangeMonth($gameStor->init_year, $gameStor->init_month, 1, $gameStor->year, $gameStor->month) && $nation['gennum'] >= GameConst::$initialNationGenLimitForRandInit) {
            $nation['availableJoin'] = false;
        }
        else if($gameStor->year < $gameStor->startyear+3 && $nation['gennum'] >= GameConst::$initialNationGenLimit){
            $nation['availableJoin'] = false;
        }
        else if($nation['scout'] == 1) {
            $nation['availableJoin'] = false;
        }
        else{
            $nation['availableJoin'] = true;
        }
    }
    unset($nation);

    $generalList = $db->query('SELECT no,name,nation,npc FROM general WHERE owner!=%i ORDER BY name ASC', $userID);
?>

국가에 임관합니다.<br>
이미 임관/등용되었던 국가는 다시 임관할 수 없습니다.<br>
바로 군주의 위치로 이동합니다.<br>
<?php if($onlyRandom): ?>
랜덤 임관 대상 국가는 아래에서 확인할 수 있습니다.<br>
<?php else: ?>
임관할 국가를 목록에서 선택하세요.<br>
<?php endif; ?>
임의의 국가로 임관할 경우 유니크 아이템을 얻을 기회가 높습니다.
임관 금지이거나 초기 제한중인 국가는 붉은색 배경으로 표시됩니다.<br>
870px x 200px를 넘어서는 내용은 표시되지 않습니다.<br>
<form name=form1 action=c_double.php method=post>
<input id='double' name='double' type='hidden'>
<input id='third' name='third' type='hidden'>
<select id='selectorV' name=value size=1 style=color:white;background-color:black>
    <optgroup label='랜덤 임관'>
        <option value=99 style=color:white;background-color:black;>임의의 국가</option>
        <option value=98 style=color:white;background-color:black;>건국된 임의 국가</option>
    </optgroup>
    <optgroup label='국가로 임관'>
        <?php foreach($nationList as $nation): ?>
            <?php if(!$nation['availableJoin']): ?>
                <option value='<?=$nation['nation']?>' style='color:<?=$nation['color']?>;background-color:red;' <?=$onlyRandom?'disabled':''?>>【 <?=$nation['name']?> 】</option>
            <?php else: ?>
                <option value='<?=$nation['nation']?>' style='color:<?=$nation['color']?>;' <?=$onlyRandom?'disabled':''?>>【 <?=$nation['name']?> 】</option>
            <?php endif; ?>
        <?php endforeach; ?>
    </optgroup>
    <optgroup label='장수를 따라 임관'>
        <?php foreach($generalList as $targetGeneral): ?>
            <option value='-<?=$targetGeneral['no']?>' <?=$onlyRandom?'disabled':''?>><?=formatName($targetGeneral['name'],$targetGeneral['npc'])?>【<?=getNationStaticInfo($targetGeneral['nation'])['name']??'재야'?>】</option>
        <?php endforeach; ?>
    </optgroup>
    
</select>
<input type=submit value=임관>
<input type=hidden name=command value=<?=$command?>>
    <?php foreach(range(0, count($turn) - 1) as $i): ?>
            <input type=hidden name=turn[] value=<?=$turn[$i]?>>
    <?php endforeach; ?>
</form>
<script>
$(function(){
    var $third = $('#third');
    var $double = $('#double');
    var $selector = $('#selectorV');
    $selector.on('change', function(){
        var value=$selector.val();
        if(value >= 98){
            $third.val(value);
            $double.val(0);
        }
        else if(value>0){
            $third.val(0);
            $double.val(value);
        }
        else{
            $third.val(1);
            $double.val(-value);
        }
    });
    $selector.val(99).trigger('change');
});
</script>
<?php
    echo getInvitationList($nationList);

    ender();
}

function command_27($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("발령", 1);

    $query = "select no,nation,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("command_27 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select no,name,city,level,npc from general where nation='{$me['nation']}' and no!='{$me['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 아국 장수를 발령합니다.<br>
아국 도시로만 발령이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=text-align:right;color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        $cityName = CityConst::byID($general['city'])->name;
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*({$cityName})</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}({$cityName})</option>";
        }
    }

    echo "
</select>
 =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=발령>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender(1);
}

function command_30($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("강행");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시로 강행합니다.<br>
최대 3칸내 도시로만 강행이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=강행>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
<input type=hidden name=turn[] value=$turn[$i]>";
}

echo "
</form>
";

printCitiesBasedOnDistance($currentcity['city'], 3);

ender();
}

function command_31($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("첩보");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 첩보를 실행합니다.<br>
인접도시일 경우 많은 정보를 얻을 수 있습니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=첩보>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    echo "
모든 도시가 가능하지만 많은 정보를 얻을 수 있는<br>
";

    printCitiesBasedOnDistance($currentcity['city'], 2);

    ender();
}

function command_32($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("화계");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 화계를 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=화계>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitiesBasedOnDistance($currentcity['city'], 2);

    ender();
}

function command_33($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("탈취");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 탈취를 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=탈취>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitiesBasedOnDistance($currentcity['city'], 2);

    ender();
}

function command_34($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("파괴");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 파괴를 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=파괴>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitiesBasedOnDistance($currentcity['city'], 2);

    ender();
}

function command_35($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("선동");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 선동을 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=선동>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitiesBasedOnDistance($currentcity['city'], 2);

    ender();
}

function command_36($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("기습");
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select city,name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
    echo "<br>
선택된 도시에 기습을 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
{$currentcity['name']} =>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=기습>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    printCitiesBasedOnDistance($currentcity['city'], 2);

    ender();
}

function command_43($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("증여");
    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level,npc from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
자신의 자금이나 군량을 다른 장수에게 증여합니다.<br>
장수를 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if    ($general['npc'] >= 2) { $style = "style=color:cyan;"; }
        elseif($general['npc'] == 1) { $style = "style=color:skyblue;"; }
        else                       { $style = ""; }
        if($general['level'] >= 9) {
            echo "
    <option value={$general['no']} {$style}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']} {$style}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<select name=fourth size=1 style=color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=12>1200</option>
    <option value=15>1500</option>
    <option value=20>2000</option>
    <option value=25>2500</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=증여>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_44($turn, $command) {
    $db = DB::db();
    $connect=$db->get();

    starter("헌납");

    echo "
자신의 자금이나 군량을 국가 재산으로 헌납합니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>
    <option value=1>금</option>
    <option value=2>쌀</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=12>1200</option>
    <option value=15>1500</option>
    <option value=20>2000</option>
    <option value=25>2500</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=헌납>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_46($turn, $command) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    starter("건국");

    $maxnation = $gameStor->maxnation;

    $colorUsed = [];
    
    foreach(GetNationColors() as $color){
        $colorUsed[$color] = 0;
    }

    $nationCount = count(getAllNationStaticInfo());
    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }

        if(!isset($nationcolor[$nation['color']])){
            $nationcolor[$nation['color']] = 1;
        }
        else{
            $nationcolor[$nation['color']]++;
        }
        
    }

    $colorUsedCnt = 0;
    foreach($colorUsed as $color=>$used){
        if($used){
            continue;
        }
        $colorUsedCnt += 1;
    }

    //색깔이 다 쓰였으면 그냥 모두 허용
    if($colorUsedCnt === count($colorUsed)){
        foreach(array_keys($colorUsed) as $color){
            $colorUsed[$color] = 0;
        }
    }


    if($nationCount < $maxnation) {
?>
현재 도시에서 나라를 세웁니다. 중, 소도시에서만 가능합니다.<br>

- 법 가 : <font color=cyan>금수입↑ 치안↑</font> <font color=magenta>인구↓ 민심↓</font><br>
- 유 가 : <font color=cyan>내정↑ 민심↑</font> <font color=magenta>쌀수입↓</font><br>
- 병 가 : <font color=cyan>기술↑ 수성↑</font> <font color=magenta>인구↓ 민심↓</font><br>
- 덕 가 : <font color=cyan>치안↑인구↑ 민심↑</font> <font color=magenta>쌀수입↓ 수성↓</font><br>
- 묵 가 : <font color=cyan>수성↑</font> <font color=magenta>기술↓</font><br>
- 도 가 : <font color=cyan>인구↑</font> <font color=magenta>기술↓ 치안↓</font><br>
- 태 평 도 : <font color=cyan>인구↑ 민심↑</font> <font color=magenta>기술↓ 수성↓</font><br>
- 오 두 미 도 : <font color=cyan>쌀수입↑ 인구↑</font> <font color=magenta>기술↓ 수성↓ 내정↓</font><br>
- 도 적 : <font color=cyan>계략↑</font> <font color=magenta>금수입↓ 치안↓ 민심↓</font><br>
- 불 가 : <font color=cyan>민심↑ 수성↑</font> <font color=magenta>금수입↓</font><br>
- 종 횡 가 : <font color=cyan>전략↑ 수성↑</font> <font color=magenta>금수입↓ 내정↓</font><br>
- 음 양 가 : <font color=cyan>내정↑ 인구↑ </font> <font color=magenta>기술↓ 전략↓</font><br>
- 명 가 : <font color=cyan>기술↑ 인구↑</font> <font color=magenta>쌀수입↓ 수성↓</font><br>

<form name=form1 action=c_double.php method=post>
국명 : <input type=text name=name size=18 maxlength=9 style=text-align:right;color:white;background-color:black>
색깔 : <select name=double size=1>
<?php
        foreach(GetNationColors() as $idx=>$color) {
            if($colorUsed[$color] > 0){
                continue;
            }
            echo "<option value={$idx} style=background-color:{$color};color:".newColor($color).";>국가명</option>";
        }

?>
</select>
성향 : <select name=third size=1>
    <option value=1 style=background-color:black;color:white;><?=getNationType(1)?></option>
    <option value=2 style=background-color:black;color:white;><?=getNationType(2)?></option>
    <option value=10 style=background-color:black;color:white;><?=getNationType(10)?></option>
    <option value=3 style=background-color:black;color:white;><?=getNationType(3)?></option>
    <option value=4 style=background-color:black;color:white;><?=getNationType(4)?></option>
    <option value=5 style=background-color:black;color:white;><?=getNationType(5)?></option>
    <option value=6 style=background-color:black;color:white;><?=getNationType(6)?></option>
    <option value=7 style=background-color:black;color:white;><?=getNationType(7)?></option>
    <option value=8 style=background-color:black;color:white;><?=getNationType(8)?></option>
    <option selected value=9 style=background-color:black;color:white;><?=getNationType(9)?></option>
    <option value=11 style=background-color:black;color:white;><?=getNationType(11)?></option>
    <option value=12 style=background-color:black;color:white;><?=getNationType(12)?></option>
    <option value=13 style=background-color:black;color:white;><?=getNationType(13)?></option>
</select>
<input type=submit value=건국>
<input type=hidden name=command value=<?=$command?>>
<?php
        for($i=0; $i < count($turn); $i++) {
            echo "<input type=hidden name=turn[] value=$turn[$i]>";
        }

        echo "</form>";
    } else {
        echo "더 이상 건국은 불가능합니다.<br>";
    }
    ender();
}

function command_48($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("장비 매매");

    $query = "select no,city,gold from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("command_48 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    $city = getCity($me['city'], "secu");

    $color = [];

    for($i=1; $i <= 6; $i++) {
        if($city['secu'] >= $i*1000) {
            $color[$i] = "white";
        } else {
            $color[$i] = "red";
        }
    }

    echo "
장비를 구입하거나 매각합니다.<br>
현재 구입 불가능한 것은 <font color=red>붉은색</font>으로 표시됩니다.<br>
현재 도시 치안 : {$city['secu']} &nbsp;&nbsp;&nbsp;현재 자금 : {$me['gold']}<br>
<form name=form1 action=c_double.php method=post>
장비 : <select name=double size=1 style=color:white;background-color:black>
    <option value=0   style=color:skyblue>_____무기매각(반값)____</option>
    <option value=1   style=color:$color[1]>".getWeapName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(1)."</option>
    <option value=2   style=color:$color[2]>".getWeapName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(2)."</option>
    <option value=3   style=color:$color[3]>".getWeapName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(3)."</option>
    <option value=4   style=color:$color[4]>".getWeapName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(4)."</option>
    <option value=5   style=color:$color[5]>".getWeapName(5)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(5)."</option>
    <option value=6   style=color:$color[6]>".getWeapName(6)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(6)."</option>
    <option value=100 style=color:skyblue>_____서적매각(반값)____</option>
    <option value=101 style=color:$color[1]>".getBookName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(1)."</option>
    <option value=102 style=color:$color[2]>".getBookName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(2)."</option>
    <option value=103 style=color:$color[3]>".getBookName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(3)."</option>
    <option value=104 style=color:$color[4]>".getBookName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(4)."</option>
    <option value=105 style=color:$color[5]>".getBookName(5)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(5)."</option>
    <option value=106 style=color:$color[6]>".getBookName(6)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(6)."</option>
    <option value=200 style=color:skyblue>_____명마매각(반값)____</option>
    <option value=201 style=color:$color[1]>".getHorseName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(1)."</option>
    <option value=202 style=color:$color[2]>".getHorseName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(2)."</option>
    <option value=203 style=color:$color[3]>".getHorseName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(3)."</option>
    <option value=204 style=color:$color[4]>".getHorseName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(4)."</option>
    <option value=205 style=color:$color[5]>".getHorseName(5)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(5)."</option>
    <option value=206 style=color:$color[6]>".getHorseName(6)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost(6)."</option>
    <option value=300 style=color:skyblue>_____도구매각(반값)____</option>
    <option value=301 style=color:$color[1]>".getItemName(1)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(1)."</option>
    <option value=302 style=color:$color[2]>".getItemName(2)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(2)."</option>
    <option value=303 style=color:$color[3]>".getItemName(3)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(3)."</option>
    <option value=304 style=color:$color[4]>".getItemName(4)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(4)."</option>
    <option value=305 style=color:$color[5]>".getItemName(5)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(5)."</option>
    <option value=306 style=color:$color[6]>".getItemName(6)."&nbsp;&nbsp;&nbsp;&nbsp;가격: ".getItemCost2(6)."</option>
</select>
<input type=submit value=거래>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_49($turn, $command) {
    $db = DB::db();
    $connect=$db->get();

    starter("군량매매");

    echo "
자신의 군량을 사거나 팝니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=third size=1 style=color:white;background-color:black>
    <option value=1>팜</option>
    <option value=2>삼</option>
</select>
<select name=double size=1 style=text-align:right;color:white;background-color:black>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=12>1200</option>
    <option value=15>1500</option>
    <option value=20>2000</option>
    <option value=25>2500</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type=submit value=거래>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_51($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("항복 권고", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select nation,power from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $myNation = MYDB_fetch_array($result);

    echo "
타국에게 항복을 권고합니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
권고할 국가를 목록에서 선택하세요.<br>
합병이 불가능한 국가는 <font color=red>붉은</font> 배경으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    $query = "select nation,name,power,color from nation where nation!='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);

        if($myNation['power'] <= $nation['power'] * 3 || !isNeighbor($me['nation'], $nation['nation'])) {
            echo "<option style='color:{$nation['color']};background-color:red;' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
        } else {
            echo "<option style='color:{$nation['color']}' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
        }
    }
    echo "
</select>
<input type=submit value=항복권고>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
제한 조건<br>
- 인접 국가<br>
- 양국 모두 외교제한 기간 없음<br>
- 제의한 국가가 항복하는 국가보다 국력 3배 초과<br>
";
    ender(1);
}

function command_52($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("원조", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $mynation = getNationStaticInfo($me['nation']);
    
    echo "
타국에게 원조합니다.<br>
작위별로 금액 제한이 있습니다.<br>
　호족: 1만<br>
　군벌: 2만<br>
주자사: 3만<br>
　주목: 4만<br>
　　공: 5만<br>
　　왕: 6만<br>
　황제: 7만<br>
원조할 국가를 목록에서 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    foreach(getAllNationStaticInfo() as $nation) {

        echo "<option style='color:{$nation['color']}' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
국고 <select name=third size=1 style=text-align:right;color:white;background-color:black>
    <option value=0>0</option>
    <option value=1>1000</option>
    <option value=2>2000</option>
    <option value=3>3000</option>
    <option value=4>4000</option>
    <option value=5>5000</option>
    <option value=6>6000</option>
    <option value=7>7000</option>
    <option value=8>8000</option>
    <option value=9>9000</option>";
    for($i=1; $i <= $mynation['level']; $i++) {
        echo "<option value={$i}0>{$i}0000</option>";
    }
    echo "
</select>
병량 <select name=fourth size=1 style=text-align:right;color:white;background-color:black>
    <option value=0>0</option>
    <option value=1>1000</option>
    <option value=2>2000</option>
    <option value=3>3000</option>
    <option value=4>4000</option>
    <option value=5>5000</option>
    <option value=6>6000</option>
    <option value=7>7000</option>
    <option value=8>8000</option>
    <option value=9>9000</option>";
    for($i=1; $i <= $mynation['level']; $i++) {
        echo "<option value={$i}0>{$i}0000</option>";
    }
    echo "
</select>
<input type=submit value=원조>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_53($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("통합제의", 1);

    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("command_53 ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select avg(power) as power,avg(gennum) as gennum from nation where level>=1";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $avgNation = MYDB_fetch_array($result);

    $query = "select std(power) as power,std(gennum) as gennum from nation where level>=1";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $stdNation = MYDB_fetch_array($result);
    
    $query = "select nation,power,gennum from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $myNation = MYDB_fetch_array($result);

    echo "
세력이 비슷한 국가에 통합 제의를 합니다. 제의한 국가 위주로 통합됩니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
통합이 불가능한 국가는 <font color=red>붉은</font> 배경으로 표시됩니다.<br>
        <form name=form1 action=c_double.php method=post>
대상국 : <select name=double size=1 style=color:white;background-color:black>";

    $query = "select nation,name,power,gennum,color from nation where nation!='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $cond1 = (-0.25 * $stdNation['power']) + $avgNation['power'];
    $cond2 = (-0.67 * $stdNation['gennum']) + $avgNation['gennum'];
    
    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);

        if(($myNation['power']+$nation['power'])/2 > $cond1 || ($myNation['gennum']+$nation['gennum'])/2 > $cond2 || !isNeighbor($me['nation'], $nation['nation'])) {
            echo "<option style='color:{$nation['color']};background-color:red;' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
        } else {
            echo "<option style='color:{$nation['color']}' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
        }
    }

    echo "
            </select>
        통합국명 : <input type=text name=nationname size=18 maxlength=9 style=text-align:right;color:white;background-color:black>
        <input type=submit value=통합제의>
        <input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
        </form>
제한 조건<br>
- 인접 국가<br>
- 양국 모두 외교제한 없음<br>
- 두 국가의 국력 평균이 상위 60%(현재 ".round($cond1,2).") 이하.<br>
- 두 국가의 장수수 평균이 상위 75%(현재 ".round($cond2,1).") 이하.<br>
";
    ender(1);
}

function command_54($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("선양");
    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select no,name,level from general where nation='{$general['nation']}' and no!='{$general['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    echo "
군주의 자리를 다른 장수에게 물려줍니다.<br>
장수를 선택하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['level'] >= 5) {
            echo "
    <option value={$general['no']}>*{$general['name']}*</option>";
        } else {
            echo "
    <option value={$general['no']}>{$general['name']}</option>";
        }
    }

    echo "
</select>
<input type=submit value=선양>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_58($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("숙련 전환");

    $dexVals = $db->queryFirstList('SELECT dex0, dex10, dex20, dex30, dex40 FROM general WHERE owner = %i', $userID);

    $dexTexts = [];
    foreach($dexVals as $idx => $val){
        $prevDexLevel = getDexCall($val);
        $newDexLevel = getDexCall(Util::toInt($val * 0.7));
        $addDex = Util::toInt(($val - $val * 0.7) * 0.5);
        $dexTexts[$idx] = "$prevDexLevel ⇒ $newDexLevel, {$addDex} 전환";
    }

    echo "
본인의 특정 병종 숙련을 30% 줄이고, 줄어든 숙련 중 2/3(20%p)를 다른 병종 숙련으로 전환합니다.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>
    <option value=0>보병 ({$dexTexts[0]})</option>
    <option value=1>궁병 ({$dexTexts[1]})</option>
    <option value=2>기병 ({$dexTexts[2]})</option>
    <option value=3>귀병 ({$dexTexts[3]})</option>
    <option value=4>차병 ({$dexTexts[4]})</option>
</select>
숙련을 
<select name=third size=1 style=color:white;background-color:black>
    <option value=0>보병 ({$dexVals[0]})</option>
    <option value=1>궁병 ({$dexVals[1]})</option>
    <option value=2>기병 ({$dexVals[2]})</option>
    <option value=3>귀병 ({$dexVals[3]})</option>
    <option value=4>차병 ({$dexVals[4]})</option>
</select>
으로
<input type=submit value=전환>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_61($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("불가침", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
타국에게 불가침을 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "red"; break;
            case 2: $color = "black"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
기간 <select name=third size=1 style=text-align:right;color:white;background-color:black>
    <option value=1 > 1</option>
    <option value=2 > 2</option>
    <option value=3 > 3</option>
    <option value=4 > 4</option>
    <option value=5 > 5</option>
    <option value=6 > 6</option>
    <option value=7 > 7</option>
    <option value=8 > 8</option>
    <option value=9 > 9</option>
    <option value=10>10</option>
    <option value=11>11</option>
    <option value=12>12</option>
    <option value=13>13</option>
    <option value=14>14</option>
    <option value=15>15</option>
    <option value=16>16</option>
    <option value=17>17</option>
    <option value=18>18</option>
    <option value=19>19</option>
    <option value=20>20</option>
</select>
년<br>
<input type=submit value=불가침>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_62($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("선전포고", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
타국에게 선전 포고합니다.<br>
선전 포고할 국가를 목록에서 선택하세요.<br>
고립되지 않은 아국 도시에서 인접한 국가에 선포 가능합니다.<br>
초반제한 해제 2년전부터 선포가 가능합니다. (체섭기준 181년 1월부터 가능)<br>
배경색은 현재 선포가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중(역시 불가)인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }
        //합병중 국가는 안됨
        $query = "select state from diplomacy where me='{$nation['nation']}' and (state='3' or state='5')";
        $tempresult = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
        $anycount = MYDB_num_rows($tempresult);
        if($anycount != 0 && $diplomacy[$nation['nation']] != 7) {
            $diplomacy[$nation['nation']] = 3;
        }
        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "red"; break;
            case 2: $color = "black"; break;
            case 3: $color = "red"; break;
            case 4: $color = "black"; break;
            case 5: $color = "red"; break;
            case 6: $color = "black"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=선전포고>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_63($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("종전", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
전쟁중인 국가에 종전을 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "black"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "red"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=종전제의>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_64($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("파기", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
불가침중인 국가에 조약 파기를 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중(제의 가능)인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "red"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=파기제의>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_65($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("초토화", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시를 초토화 시킵니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=초토화>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_66($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("천도", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시로 천도합니다.<br>
현재 수도에서 인접한 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=천도>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_67($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("증축", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시를 증축합니다.<br>
현재 수도만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=증축>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_68($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("감축", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시를 감축합니다.<br>
현재 수도만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=감축>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_72($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("백성동원", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시에 백성동원을 발동합니다.<br>
아국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=백성동원>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_73($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("수몰", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시에 수몰을 발동합니다.<br>
전쟁중인 상대국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=수몰>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_74($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("허보", 1);
    $query = "select city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name,path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $currentcity = MYDB_fetch_array($result);

    echo getMapHtml();
echo "<br>
선택된 도시에 허보를 발동합니다.<br>
선포, 전쟁중인 상대국 도시만 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<form name=form1 action=c_double.php method=post>
<select name=double size=1 style=color:white;background-color:black>";

    echo optionsForCities();

    echo "
</select>
<input type=submit value=허보>
<input type=hidden name=command value=$command>";

    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";

    ender();
}

function command_75($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("피장파장", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
선택된 국가에 피장파장을 발동합니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 피장파장 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "black"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=피장파장>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_77($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("이호경식", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
선택된 국가에 이호경식을 발동합니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 이호경식 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "black"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=이호경식>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_78($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    starter("급습", 1);

    $query = "select nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    echo "
선택된 국가에 급습을 발동합니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 급습 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<form name=form1 action=c_double.php method=post>
 대상 국가 <select name=double size=1 style=color:white;background-color:black>";

    $query = "select you,state from diplomacy where me='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    $diplomacy = [];

    for($i=1; $i <= $count; $i++) {
        $dip = MYDB_fetch_array($result);
        $diplomacy[$dip['you']] = $dip['state'];
    }

     foreach(getAllNationStaticInfo() as $nation){
        if($nation['nation'] == $me['nation']){
            continue;
        }

        switch($diplomacy[$nation['nation']]) {
            case 0: $color = "red"; break;
            case 1: $color = "black"; break;
            case 2: $color = "red"; break;
            case 3: $color = "red"; break;
            case 4: $color = "red"; break;
            case 5: $color = "red"; break;
            case 6: $color = "red"; break;
            case 7: $color = "blue"; break;
        }
        echo "<option style='background-color:$color;color:{$nation['color']};' value='{$nation['nation']}'>【 {$nation['name']} 】</option>";
    }
    echo "
</select>
<input type=submit value=급습>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}

function command_81($turn, $command) {
    $db = DB::db();
    $connect=$db->get();

    starter("국기변경", 1);

    $colorUsed = [];
    
    foreach(GetNationColors() as $color){
        $colorUsed[$color] = 0;
    }

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        
        $colorUsed[$nation['color']]+=1;
    }

    $colorUsedCnt = 0;
    foreach($colorUsed as $color=>$used){
        if($used){
            continue;
        }
        $colorUsedCnt += 1;
    }

    //색깔이 다 쓰였으면 그냥 모두 허용
    if($colorUsedCnt === count($colorUsed)){
        foreach(array_keys($colorUsed) as $color){
            $colorUsed[$color] = 0;
        }
    }

    echo "
국기를 변경합니다. 단 1회 가능합니다.<br>
<form name=form1 action=c_double.php method=post>
색깔 : <select name=double size=1>";
    foreach(GetNationColors() as $idx=>$color) {
        if($colorUsed[$color]>0){
            continue;
        }
        echo "<option value={$idx} style=background-color:{$color};color:".newColor($color).";>국가명</option>";
    }
    echo "
</select>
<input type=submit value=국기변경>
<input type=hidden name=command value=$command>";
    for($i=0; $i < count($turn); $i++) {
        echo "
            <input type=hidden name=turn[] value=$turn[$i]>";
    }

    echo "
</form>
";
    ender(1);
}
