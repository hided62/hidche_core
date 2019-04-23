<?php
namespace sammo;

require_once 'process_war.php';
require_once 'func_gamerule.php';
require_once 'func_process.php';
require_once 'func_process_chief.php';
require_once 'func_tournament.php';
require_once 'func_auction.php';
require_once 'func_string.php';
require_once 'func_history.php';
require_once 'func_legacy.php';
require_once 'func_converter.php';
require_once 'func_time_event.php';
require_once('func_template.php');
require_once('func_message.php');
require_once('func_map.php');
require_once('func_command.php');

/**
 * nationID를 이용하여 국가의 '어지간해선' 변경되지 않는 정보(이름, 색, 성향, 규모, 수도)를 반환해줌
 * 
 * @param int|null $nationID 국가 코드, -1인 경우 전체, null인 경우 수행하지 않음. 0인 경우에는 재야임
 * @param bool $forceRefresh 강제 갱신 여부
 * 
 * @return array|null nationID에 해당하는 국가가 있을 경우 array 반환. 그외의 경우 null
 */
function getNationStaticInfo($nationID, $forceRefresh=false)
{
    static $nationList = null;

    if ($forceRefresh) {
        $nationList = null;
    }

    if ($nationID === null) {
       return null;
    }
    if($nationID === 0){
        return [
            'nation'=>0,
            'name'=>'재야',
            'color'=>'#000000',
            'type'=>GameConst::$neutralNationType,
            'level'=>0,
            'capital'=>0
        ];
    }

    if($nationList === null){
        $nationAll = DB::db()->query("select nation, name, color, type, level, capital from nation");
        $nationList = Util::convertArrayToDict($nationAll, "nation");
        $nationList[-1] = $nationAll;
    }

    if(isset($nationList[$nationID])){
        return $nationList[$nationID];
    }
    return null;
}

/**
 * getNationStaticInfo() 함수의 국가 캐시를 초기화
 */
function refreshNationStaticInfo(){
    getNationStaticInfo(null, true);
}

/**
 * getNationStaticInfo(-1) 의 단축형
 */
function getAllNationStaticInfo(){
    return getNationStaticInfo(-1);
}

function GetImageURL($imgsvr, $filepath='') {
    if($imgsvr == 0) {
        return ServConfig::getSharedIconPath($filepath);
    } else {
        return AppConf::getUserIconPathWeb($filepath);
    }
}

/**
 * @param null|int $con 장수의 벌점
 * @param null|int $conlimit 최대 벌점
 */
function checkLimit($con = null) {
    $session = Session::getInstance();
    if($session->userGrade>=4){
        return 0;
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    if($con === null){
        $con = $db->queryFirstField('SELECT con FROM general WHERE `owner`=%i', Session::getUserID());
    }
    $conlimit = $gameStor->conlimit;

    if($con > $conlimit) {
        return 2;
    //접속제한 90%이면 경고문구
    } elseif($con > $conlimit * 0.9) {
        return 1;
    } else {
        return 0;
    }
}

function getBlockLevel() {
    return DB::db()->queryFirstField('select block from general where no = %i', Session::getInstance()->generalID);
}

function getRandGenName() {
    $first = array('가', '간', '감', '강', '고', '공', '공손', '곽', '관', '괴', '교', '금', '노', '뇌', '능', '도', '동', '두',
        '등', '마', '맹', '문', '미', '반', '방', '부', '비', '사', '사마', '서', '설', '성', '소', '손', '송', '순', '신', '심',
        '악', '안', '양', '엄', '여', '염', '오', '왕', '요', '우', '원', '위', '유', '육', '윤', '이', '장', '저', '전', '정',
        '제갈', '조', '종', '주', '진', '채', '태사', '하', '하후', '학', '한', '향', '허', '호', '화', '황',
        '공손', '손', '왕', '유', '장', '조');
    $last = array('가', '간', '강', '거', '건', '검', '견', '경', '공', '광', '권', '규', '녕', '단', '대', '도', '등', '람',
        '량', '례', '로', '료', '모', '민', '박', '범', '보', '비', '사', '상', '색', '서', '소', '속', '송', '수', '순', '습',
        '승', '양', '연', '영', '온', '옹', '완', '우', '웅', '월', '위', '유', '윤', '융', '이', '익', '임', '정', '제', '조',
        '주', '준', '지', '찬', '책', '충', '탁', '택', '통', '패', '평', '포', '합', '해', '혁', '현', '화', '환', '회', '횡',
        '후', '훈', '휴', '흠', '흥');

    $firstname = $first[rand()%count($first)];
    $lastname = $last[rand()%count($last)];

    $fullname = "{$firstname}{$lastname}";
    return $fullname;
}



function cityInfo() {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $query = "select no,city from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    // 도시 정보
    $city = getCity($me['city']);

    $nation = getNationStaticInfo($city['nation']);

    $pop  = $city['pop'] / $city['pop2'] * 100;
    $trust = $city['trust'];
    $agri = $city['agri'] / $city['agri2'] * 100;
    $comm = $city['comm'] / $city['comm2'] * 100;
    $secu = $city['secu'] / $city['secu2'] * 100;
    $def  = $city['def'] / $city['def2'] * 100;
    $wall = $city['wall'] / $city['wall2'] * 100;
    if($city['trade'] === null) {
        $trade = 0;
        $tradeStr = "상인없음";
    } else {
        $trade = ($city['trade']-95) * 10;
        $tradeStr = $city['trade'] . "%";
    }

    if(!$nation){
        $nation = getNationStaticInfo(0);
    }

    if($nation['color'] == "" ) { $nation['color'] = "#000000"; }
    echo "<table style='width:100%;' class='tb_layout bg2'>
    <tr><td colspan=8 style=text-align:center;height:20px;color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13px;>【 ".CityConst::$regionMap[$city['region']]." | ".CityConst::$levelMap[$city['level']]." 】 {$city['name']}</td></tr>
    <tr><td colspan=8 style=text-align:center;height:20px;color:".newColor($nation['color']).";background-color:{$nation['color']}><b>";

    if($city['nation'] == 0) {
        echo "공 백 지";
    } else {
        echo "지배 국가 【 {$nation['name']} 】";
    }

    if($city['gen1'] > 0) {
        $query = "select name from general where no='{$city['gen1']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen1 = MYDB_fetch_array($result);
    } else {
        $gen1 = ['name'=>'-'];
    }

    if($city['gen2'] > 0) {
        $query = "select name from general where no='{$city['gen2']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen2 = MYDB_fetch_array($result);
    } else {
        $gen2 = ['name'=>'-'];
    }

    if($city['gen3'] > 0) {
        $query = "select name from general where no='{$city['gen3']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen3 = MYDB_fetch_array($result);
    } else {
        $gen3 = ['name'=>'-'];
    }

    echo "
        </b></td>
    </tr>
    <tr>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>주민</b></td>
        <td height=7 colspan=3>".bar($pop)."</td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>민심</b></td>
        <td height=7>".bar($trust)."</td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>태수</b></td>
        <td rowspan=2 style='text-align:center;'>{$gen1['name']}</td>
    </tr>
    <tr>
        <td colspan=3 style='text-align:center;'>{$city['pop']}/{$city['pop2']}</td>
        <td style='text-align:center;'>".round($city['trust'], 1)."</td>
    </tr>
    <tr>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>농업</b></td>
        <td width=100 height=7>".bar($agri)."</td>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>상업</b></td>
        <td width=100 height=7>".bar($comm)."</td>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>치안</b></td>
        <td width=100 height=7>".bar($secu)."</td>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>군사</b></td>
        <td rowspan=2 style='text-align:center;'>{$gen2['name']}</td>
    </tr>
    <tr>
        <td style='text-align:center;'>{$city['agri']}/{$city['agri2']}</td>
        <td style='text-align:center;'>{$city['comm']}/{$city['comm2']}</td>
        <td style='text-align:center;'>{$city['secu']}/{$city['secu2']}</td>
    </tr>
    <tr>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>수비</b></td>
        <td height=7>".bar($def)."</td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>성벽</b></td>
        <td height=7>".bar($wall)."</td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>시세</b></td>
        <td height=7>".bar($trade)."</td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>종사</b></td>
        <td rowspan=2 style='text-align:center;'>{$gen3['name']}</td>
    </tr>
    <tr>
        <td style='text-align:center;'>{$city['def']}/{$city['def2']}</td>
        <td style='text-align:center;'>{$city['wall']}/{$city['wall2']}</td>
        <td style='text-align:center;'>{$tradeStr}</td>
    </tr>
</table>
";
}

function myNationInfo() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $userID = Session::getUserID();

    $admin = $gameStor->getValues(['startyear','year']);

    $query = "select no,nation from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select nation,name,color,power,msg,gold,rice,bill,rate,scout,war,strategic_cmd_limit,surlimit,tech,level,type from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select COUNT(*) as cnt, SUM(pop) as totpop, SUM(pop2) as maxpop from city where nation='{$nation['nation']}'"; // 도시 이름 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select COUNT(*) as cnt, SUM(crew) as totcrew,SUM(leader)*100 as maxcrew from general where nation='{$nation['nation']}'";    // 장수 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name from general where nation='{$nation['nation']}' and level='12'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level12 = MYDB_fetch_array($genresult);

    $query = "select name from general where nation='{$nation['nation']}' and level='11'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level11 = MYDB_fetch_array($genresult);

    echo "<table width=498 class='tb_layout bg2 nation_info'>
    <tr>
        <td colspan=4 ";

    if($me['nation'] == 0) { echo "style='color:white;background-color:000000;font-weight:bold;font-size:13px;text-align:center;'>【재 야】"; }
    else { echo "style='color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13px;text-align:center'>국가【 {$nation['name']} 】"; }

    echo "
        </td>
    </tr>
    <tr>
        <td class='bg1 center'><b>성 향</b></td>
        <td colspan=3 class='center'><font color=\"yellow\">".getNationType($nation['type'])."</font> (".getNationType2($nation['type']).")</td>
        </td>
    </tr>
    <tr>
        <td width=68 class='bg1 center'><b>".getLevel(12, $nation['level'])."</b></td>
        <td width=178 class='center'>";echo $level12?$level12['name']:"-"; echo "</td>
        <td width=68 class='bg1 center'><b>".getLevel(11, $nation['level'])."</b></td>
        <td width=178 class='center'>";echo $level11?$level11['name']:"-"; echo "</td>
    </tr>
    <tr>
        <td class='bg1 center'><b>총주민</b></td>
        <td class='center'>";echo $me['nation']==0?"해당 없음":"{$city['totpop']}/{$city['maxpop']}";echo "</td>
        <td class='bg1 center'><b>총병사</b></td>
        <td class='center'>";echo $me['nation']==0?"해당 없음":"{$general['totcrew']}/{$general['maxcrew']}"; echo "</td>
        </td>
    </tr>
    <tr>
        <td class='bg1 center'><b>국 고</b></td>
        <td class='center'>";echo $me['nation']==0?"해당 없음":"{$nation['gold']}";echo "</td>
        <td class='bg1 center'><b>병 량</b></td>
        <td class='center'>";echo $me['nation']==0?"해당 없음":"{$nation['rice']}";echo "</td>
    </tr>
    <tr>
        <td class='bg1 center'><b>지급률</b></td>
        <td class='center'>";
    if($me['nation'] == 0) {
        echo "해당 없음";
    } else {
        echo $nation['bill']==0?"0 %":"{$nation['bill']} %";
    }
    echo "
        </td>
        <td class='bg1 center'><b>세 율</b></td>
        <td class='center'>";
    if($me['nation'] == 0) {
        echo "해당 없음";
    } else {
        echo $nation['rate']==0?"0 %":"{$nation['rate']} %";
    }

    $techCall = getTechCall($nation['tech']);

    if(TechLimit($admin['startyear'], $admin['year'], $nation['tech'])) { $nation['tech'] = "<font color=magenta>".floor($nation['tech'])."</font>"; }
    else { $nation['tech'] = "<font color=limegreen>".floor($nation['tech'])."</font>"; }

    $nation['tech'] = "$techCall / {$nation['tech']}";
    
    if($me['nation']==0){
        $nation['strategic_cmd_limit'] = "<font color=white>해당 없음</font>";
        $nation['surlimit'] = "<font color=white>해당 없음</font>";
        $nation['scout'] = "<font color=white>해당 없음</font>";
        $nation['war'] = "<font color=white>해당 없음</font>";
        $nation['power'] = "<font color=white>해당 없음</font>";
    } else {
        if($nation['strategic_cmd_limit'] != 0) { $nation['strategic_cmd_limit'] = "<font color=red>{$nation['strategic_cmd_limit']}턴</font>"; }
        else { $nation['strategic_cmd_limit'] = "<font color=limegreen>가 능</font>"; }
    
        if($nation['surlimit'] != 0) { $nation['surlimit'] = "<font color=red>{$nation['surlimit']}턴</font>"; }
        else { $nation['surlimit'] = "<font color=limegreen>가 능</font>"; }
    
        if($nation['scout'] != 0) { $nation['scout'] = "<font color=red>금 지</font>"; }
        else { $nation['scout'] = "<font color=limegreen>허 가</font>"; }
    
        if($nation['war'] != 0) { $nation['war'] = "<font color=red>금 지</font>"; }
        else { $nation['war'] = "<font color=limegreen>허 가</font>"; }
    
        
    }

    echo "
        </td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>속 령</b></td>
        <td style='text-align:center;'>";echo $me['nation']==0?"-":"{$city['cnt']}"; echo "</td>
        <td style='text-align:center;' class='bg1'><b>장 수</b></td>
        <td style='text-align:center;'>";echo $me['nation']==0?"-":"{$general['cnt']}"; echo "</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>국 력</b></td>
        <td style='text-align:center;'>{$nation['power']}</td>
        <td style='text-align:center;' class='bg1'><b>기술력</b></td>
        <td style='text-align:center;'>";echo $me['nation']==0?"-":"{$nation['tech']}"; echo "</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>전 략</b></td>
        <td style='text-align:center;'>{$nation['strategic_cmd_limit']}</td>
        <td style='text-align:center;' class='bg1'><b>외 교</b></td>
        <td style='text-align:center;'>{$nation['surlimit']}</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>임 관</b></td>
        <td style='text-align:center;'>{$nation['scout']}</td>
        <td style='text-align:center;' class='bg1'><b>전 쟁</b></td>
        <td style='text-align:center;'>{$nation['war']}</td>
    </tr>
</table>
";
}

function checkSecretMaxPermission($penalty){
    $secretMax = 4;
    if($penalty['noTopSecret']??false){
        $secretMax = 1;
    }
    else if($penalty['noChief']??false){
        $secretMax = 1;
    }
    else if($penalty['noAmbassador']??false){
        $secretMax = 2;
    }
    return $secretMax;
}

function checkSecretPermission($me, $checkSecretLimit=true){
    if(!key_exists('penalty', $me) || !key_exists('permission', $me)){
        trigger_error ('canAccessSecret() 함수에 필요한 인자가 부족');
    }
    $penalty = Json::decode($me['penalty'])??[];
    $permission = $me['permission'];

    if(!$me['nation']){
        return -1;
    }

    if($me['level'] == 0){
        return -1;
    }
    

    if($penalty['noSecret']??false){
        return 0;
    }

    $secretMin = 0;
    $secretMax = checkSecretMaxPermission($me, $penalty);
    

    if($me['level'] == 12){
        $secretMin = 4;
    }
    else if($me['permission'] == 'ambassador'){
        $secretMin = 4;
    }
    else if($me['permission'] == 'auditor'){
        $secretMin = 3;
    }
    else if($me['level'] >= 5){
        $secretMin = 2;
    }
    else if($me['level'] > 1){
        $secretMin = 1;
    }
    else if($checkSecretLimit){
        $db = DB::db();
        $secretLimit = $db->queryFirstField('SELECT secretlimit FROM nation WHERE nation = %i', $me['nation']);
        if ($me['belong'] >= $secretLimit) {
            $secretMin = 1;
        }
    }

    return min($secretMin, $secretMax);
}

function addCommand($typename, $value, $valid = 1, $color=0) {
    if($valid == 1) {
        switch($color) {
            case 0:
                echo "
    <option style='color:white;background-color:black;' value='{$value}'>{$typename}</option>";
                break;
            case 1:
                echo "
    <option style='color:skyblue;background-color:black;' value='{$value}'>{$typename}</option>";
                break;
            case 2:
                echo "
    <option style='color:orange;background-color:black;' value='{$value}'>{$typename}</option>";
                break;
        }
    } else {
        echo "
    <option style='color:white;background-color:red;' value='{$value}'>{$typename}(불가)</option>";
    }
}

function commandGroup($typename, $type=0) {
    if($type == 0) {
        echo "
    <optgroup label='{$typename}' style='color:skyblue;background-color:black;'>";
    } else {
        echo "
    </optgroup>";
    }
}

function printCommandTable(General $generalObj) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $gameStor->turnOnCache();
    $env = $gameStor->getAll();

?>
<select id='generalCommandList' name='commandtype' size=1 style='height:20px;width:260px;color:white;background-color:black;font-size:12px;'>";
<?php

    $getCompensateClassName = function($value){
        if($value > 0){
            return 'compensatePositive';
        }
        else if($value < 0){
            return 'compensateNegative';
        }
        return 'compensateNeutral';
    };

    foreach(GameConst::$availableGeneralCommand as $commandCategory => $commandList){
        if($commandCategory){
            commandGroup("======= {$commandCategory} =======");
        }

        foreach($commandList as $commandClassName){
            $commandObj = buildGeneralCommandClass($commandClassName, $generalObj, $env);
            if(!$commandObj->canDisplay()){
                continue;
            }
?>
<option 
    class='commandBasic <?=$commandObj->getCompensationStyle()?> <?=$commandObj->isReservable()?'':'commandImpossible'?>'
    value='<?=Util::getClassNameFromObj($commandObj)?>'
    data-reqArg='<?=($commandObj::$reqArg)?'true':'false'?>'
><?=$commandObj->getCommandDetailTitle()?><?=$commandObj->isReservable()?'':'(불가)'?></option>
<?php
        }

        if($commandCategory){
            commandGroup('', 1);
        }
    }

?>
</select>
<?php
}

function chiefCommandTable() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $develcost = $gameStor->develcost;

    $me = $db->queryFirstRow('select no,nation,city,level from general where owner=%i', $userID);
    $nation = $db->queryFirstRow('SELECT level,can_change_flag,gennum FROM nation WHERE nation=%i', $me['nation']);

    $genCount = $nation['gennum'];
    $citySupply = $db->queryFirstField('SELECT supply FROM city WHERE city=%i', $me['city']);

    if($nation['level'] > 0) { $valid = 1; }
    else { $valid = 0; }
    if($citySupply == 0) { $valid = 0; }

    echo "
<select name=commandtype size=1 style='height:20px;color:white;background-color:black;font-size:13px;'>";
    commandGroup("====== 휴 식 ======");
    addCommand("휴 식", 99);
    commandGroup("", 1);
    commandGroup("====== 인 사 ======");
    addCommand("발령", 27, $valid);
    addCommand("포상", 23, $valid);
    addCommand("몰수", 24, $valid);
    commandGroup("", 1);
    commandGroup("====== 외 교 ======");
    //addCommand("통합 제의", 53, $valid);

    addCommand("항복 권고", 51, $valid);
    if($nation['level'] >= 2) {
        addCommand("물자 원조", 52, $valid);
    } else {
        addCommand("물자 원조", 52, 0);
    }
    addCommand("불가침 제의", 61, $valid);
    addCommand("선전 포고", 62, $valid);
    addCommand("종전 제의", 63, $valid);
    addCommand("파기 제의", 64, $valid);
    commandGroup("", 1);
    commandGroup("====== 특 수 ======");
    addCommand("초토화", 65, $valid);
    addCommand("천도/3턴(금쌀{$develcost}0)", 66, $valid);
    $cost = $develcost * 500 + 60000;   // 7만~13만
    addCommand("증축/6턴(금쌀{$cost})", 67, $valid);
    addCommand("감축/6턴", 68, $valid);
    commandGroup("", 1);
    commandGroup("====== 전 략 ======");
    $term = Util::round(sqrt($genCount*8)*10);
    addCommand("필사즉생/3턴(전략{$term})", 71, $valid);
    $term = Util::round(sqrt($genCount*4)*10);
    addCommand("백성동원/1턴(전략{$term})", 72, $valid);
    $term = Util::round(sqrt($genCount*4)*10);
    addCommand("수몰/3턴(전략{$term})", 73, $valid);
    $term = Util::round(sqrt($genCount*4)*10);
    addCommand("허보/2턴(전략{$term})", 74, $valid);
    $term = Util::round(sqrt($genCount*2)*10);
    if($term < 72) { $term = 72; }
    addCommand("피장파장/3턴(전략{$term})", 75, $valid);
    $term = Util::round(sqrt($genCount*10)*10);
    addCommand("의병모집/3턴(전략{$term})", 76, $valid);
    $term = Util::round(sqrt($genCount*16)*10);
    addCommand("이호경식/1턴(전략{$term})", 77, $valid);
    $term = Util::round(sqrt($genCount*16)*10);
    addCommand("급습/1턴(전략{$term})", 78, $valid);
    commandGroup("", 1);
    commandGroup("====== 기 타 ======");
    if($nation['can_change_flag'] > 0) {
        addCommand("국기 변경", 81, 1);
    } else {
        addCommand("국기 변경", 81, 0);
    }
    commandGroup("", 1);
    echo "
</select>
";
}

function myInfo(General $generalObj) {
    generalInfo($generalObj->getID());
}

function generalInfo($no) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $show_img_level = $gameStor->show_img_level;

    $query = "select block,no,name,picture,imgsvr,injury,nation,city,troop,leader,leader2,power,power2,intel,intel2,explevel,experience,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,killturn,age,personal,special,specage,special2,specage2,mode,con,connect from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }
    if($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    $troop = getTroop($general['troop']);

    $level = getLevel($general['level'], $nation['level']);
    if($general['level'] == 2)     {
        $query = "select name from city where gen3='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
        $level = $city['name']." ".$level;
    } elseif($general['level'] == 3) {
        $query = "select name from city where gen2='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
        $level = $city['name']." ".$level;
    } elseif($general['level'] == 4) {
        $query = "select name from city where gen1='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
        $level = $city['name']." ".$level;
    }
    $call = getCall($general['leader'], $general['power'], $general['intel']);
    $typename = GameUnitConst::byId($general['crewtype'])->name;
    $weapname = getWeapName($general['weap']);
    $bookname = getBookName($general['book']);
    $horsename = getHorseName($general['horse']);
    $itemname = displayItemInfo($general['item']);
    if($general['injury'] > 0) {
        $leader = intdiv($general['leader'] * (100 - $general['injury']), 100);
        $power = intdiv($general['power'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
    } else {
        $leader = $general['leader'];
        $power = $general['power'];
        $intel = $general['intel'];
    }
    if($general['injury'] > 60)     { $color = "<font color=red>";     $injury = "위독"; }
    elseif($general['injury'] > 40) { $color = "<font color=magenta>"; $injury = "심각"; }
    elseif($general['injury'] > 20) { $color = "<font color=orange>";  $injury = "중상"; }
    elseif($general['injury'] > 0)  { $color = "<font color=yellow>";  $injury = "경상"; }
    else                     { $color = "<font color=white>";   $injury = "건강"; }

    $remaining = substr($general['turntime'], 14, 2) - date('i');
    if($remaining < 0) { $remaining = 60 + $remaining; }

    if($nation['color'] == "") { $nation['color'] = "#000000"; }

    if($general['age'] < GameConst::$retirementYear*0.75)     { $general['age'] = "<font color=limegreen>{$general['age']} 세</font>"; }
    elseif($general['age'] < GameConst::$retirementYear) { $general['age'] = "<font color=yellow>{$general['age']} 세</font>"; }
    else                  { $general['age'] = "<font color=red>{$general['age']} 세</font>"; }

    $general['connect'] = Util::round($general['connect'] / 10) * 10;
    $special = $general['special'] == GameConst::$defaultSpecialDomestic ? "{$general['specage']}세" : "<font color=limegreen>".displaySpecialDomesticInfo($general['special'])."</font>";
    $special2 = $general['special2'] == 0 ? "{$general['specage2']}세" : "<font color=limegreen>".displaySpecialWarInfo($general['special2'])."</font>";

    switch($general['personal']) {
        case  2:    case  4:
            $atmos = "<font color=cyan>{$general['atmos']} (+5)</font>"; break;
        case  0:    case  9:    case 10:
            $atmos = "<font color=magenta>{$general['atmos']} (-5)</font>"; break;
        default:
            $atmos = "{$general['atmos']}"; break;
    }
    switch($general['personal']) {
        case  3:    case  5:
            $train = "<font color=cyan>{$general['train']} (+5)</font>"; break;
        case  1:    case  8:    case 10:
            $train = "<font color=magenta>{$general['train']} (-5)</font>"; break;
        default:
            $train = "{$general['train']}"; break;
    }
    if($general['troop'] == 0)    { $troop['name'] = "-"; }
    if($general['mode'] == 2)     { $general['mode'] = "<font color=limegreen>수비 함(훈사80)</font>"; }
    elseif($general['mode'] == 1) { $general['mode'] = "<font color=limegreen>수비 함(훈사60)</font>"; }
    else                        { $general['mode'] = "<font color=red>수비 안함</font>"; }

    $weapImage = ServConfig::$gameImagePath."/weap{$general['crewtype']}.png";
    if($show_img_level < 2) { $weapImage = ServConfig::$sharedIconPath."/default.jpg"; };
    $imageTemp = GetImageURL($general['imgsvr']);
    echo "<table width=498 class='tb_layout bg2'>
    <tr>
        <td width=64 height=64 rowspan=3 class='generalIcon' style='text-align:center;background:no-repeat center url(\"{$imageTemp}/{$general['picture']}\");background-size:64px;'>&nbsp;</td>
        <td colspan=9 height=16 style=text-align:center;color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13px;>{$general['name']} 【 {$level} | {$call} | {$color}{$injury}</font> 】 ".substr($general['turntime'], 11)."</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>통솔</b></td>
        <td style='text-align:center;'>&nbsp;{$color}{$leader}</font>{$lbonus}&nbsp;</td>
        <td style='text-align:center;' width=45>".bar(expStatus($general['leader2']), 20)."</td>
        <td style='text-align:center;' class='bg1'><b>무력</b></td>
        <td style='text-align:center;'>&nbsp;{$color}{$power}</font>&nbsp;</td>
        <td style='text-align:center;' width=45>".bar(expStatus($general['power2']), 20)."</td>
        <td style='text-align:center;' class='bg1'><b>지력</b></td>
        <td style='text-align:center;'>&nbsp;{$color}{$intel}</font>&nbsp;</td>
        <td style='text-align:center;' width=45>".bar(expStatus($general['intel2']), 20)."</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>명마</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$horsename</font></td>
        <td style='text-align:center;' class='bg1'><b>무기</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$weapname</font></td>
        <td style='text-align:center;' class='bg1'><b>서적</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$bookname</font></td>
    </tr>
    <tr>
        <td height=64 rowspan=3 style='text-align:center;background:no-repeat center url(\"{$weapImage}\");background-size:64px;'></td>
        <td style='text-align:center;' class='bg1'><b>자금</b></td>
        <td style='text-align:center;' colspan=2>{$general['gold']}</td>
        <td style='text-align:center;' class='bg1'><b>군량</b></td>
        <td style='text-align:center;' colspan=2>{$general['rice']}</td>
        <td style='text-align:center;' class='bg1'><b>도구</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$itemname</font></td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>병종</b></td>
        <td style='text-align:center;' colspan=2>$typename</td>
        <td style='text-align:center;' class='bg1'><b>병사</b></td>
        <td style='text-align:center;' colspan=2>{$general['crew']}</td>
        <td style='text-align:center;' class='bg1'><b>성격</b></td>
        <td style='text-align:center;' colspan=2>".displayCharInfo($general['personal'])."</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>훈련</b></td>
        <td style='text-align:center;' colspan=2>$train</td>
        <td style='text-align:center;' class='bg1'><b>사기</b></td>
        <td style='text-align:center;' colspan=2>$atmos</td>
        <td style='text-align:center;' class='bg1'><b>특기</b></td>
        <td style='text-align:center;' colspan=2>$special / $special2</td>
    </tr>
    <tr height=20>
        <td style='text-align:center;' class='bg1'><b>Lv</b></td>
        <td style='text-align:center;'>&nbsp;{$general['explevel']}&nbsp;</td>
        <td style='text-align:center;' colspan=5>".bar(getLevelPer($general['experience'], $general['explevel']), 20)."</td>
        <td style='text-align:center;' class='bg1'><b>연령</b></td>
        <td style='text-align:center;' colspan=2>{$general['age']}</td>
    </tr>
    <tr height=20>
        <td style='text-align:center;' class='bg1'><b>수비</b></td>
        <td style='text-align:center;' colspan=3>{$general['mode']}</td>
        <td style='text-align:center;' class='bg1'><b>삭턴</b></td>
        <td style='text-align:center;' colspan=2>{$general['killturn']} 턴</td>
        <td style='text-align:center;' class='bg1'><b>실행</b></td>
        <td style='text-align:center;' colspan=2>$remaining 분 남음</td>
    </tr>
    <tr height=20>
        <td style='text-align:center;' class='bg1'><b>부대</b></td>
        <td style='text-align:center;' colspan=3>{$troop['name']}</td>
        <td style='text-align:center;' class='bg1'><b>벌점</b></td>
        <td style='text-align:center;' colspan=5>".getConnect($general['connect'])." {$general['connect']}({$general['con']})</td>
    </tr>
</table>";
}

function myInfo2() {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $query = "select no from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    generalInfo2($me['no']);
}

function generalInfo2($no) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select personal,experience,dedication,firenum,warnum,killnum,deathnum,killcrew,deathcrew,belong,killnum*100/warnum as winrate,killcrew/deathcrew*100 as killrate,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $general['winrate'] = round($general['winrate'], 2);
    $general['killrate'] = round($general['killrate'], 2);

    switch($general['personal']) {
        case  0:    case  1;    case  6:
            $experience = "<font color=cyan>".getHonor($general['experience'])." ({$general['experience']})</font>"; break;
        case  4:    case  5:    case  7:    case 10:
            $experience = "<font color=magenta>".getHonor($general['experience'])." ({$general['experience']})</font>"; break;
        default:
            $experience = getHonor($general['experience'])." ({$general['experience']})"; break;
    }
    switch($general['personal']) {
        case 10:
            $dedication = "<font color=magenta>".getDed($general['dedication'])." ({$general['dedication']})</font>"; break;
        default:
            $dedication = getDed($general['dedication'])." ({$general['dedication']})"; break;
    }

    $dex0  = $general['dex0']  / GameConst::$dexLimit * 100;
    $dex10 = $general['dex10'] / GameConst::$dexLimit * 100;
    $dex20 = $general['dex20'] / GameConst::$dexLimit * 100;
    $dex30 = $general['dex30'] / GameConst::$dexLimit * 100;
    $dex40 = $general['dex40'] / GameConst::$dexLimit * 100;

    if($dex0 > 100) { $dex0 = 100; }
    if($dex10 > 100) { $dex10 = 100; }
    if($dex20 > 100) { $dex20 = 100; }
    if($dex30 > 100) { $dex30 = 100; }
    if($dex40 > 100) { $dex40 = 100; }

    $general['dex0_text']  = getDexCall($general['dex0']);
    $general['dex10_text'] = getDexCall($general['dex10']);
    $general['dex20_text'] = getDexCall($general['dex20']);
    $general['dex30_text'] = getDexCall($general['dex30']);
    $general['dex40_text'] = getDexCall($general['dex40']);

    $general['dex0_short'] = sprintf('%.1fK', $general['dex0']/1000);
    $general['dex10_short'] = sprintf('%.1fK', $general['dex10']/1000);
    $general['dex20_short'] = sprintf('%.1fK', $general['dex20']/1000);
    $general['dex30_short'] = sprintf('%.1fK', $general['dex30']/1000);
    $general['dex40_short'] = sprintf('%.1fK', $general['dex40']/1000);

    echo "<table width=498 class='tb_layout bg2'>
    <tr><td style='text-align:center;' colspan=6 class='bg1'><b>추 가 정 보</b></td></tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>명성</b></td>
        <td style='text-align:center;'>$experience</td>
        <td style='text-align:center;' class='bg1'><b>계급</b></td>
        <td style='text-align:center;' colspan=3>$dedication</td>
    </tr>
    <tr>
        <td width=64 style='text-align:center;' class='bg1'><b>전투</b></td>
        <td width=132 style='text-align:center;'>{$general['warnum']}</td>
        <td width=48 style='text-align:center;' class='bg1'><b>계략</b></td>
        <td width=98 style='text-align:center;'>{$general['firenum']}</td>
        <td width=48 style='text-align:center;' class='bg1'><b>사관</b></td>
        <td width=98 style='text-align:center;'>{$general['belong']}년</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>승률</b></td>
        <td style='text-align:center;'>{$general['winrate']} %</td>
        <td style='text-align:center;' class='bg1'><b>승리</b></td>
        <td style='text-align:center;'>{$general['killnum']}</td>
        <td style='text-align:center;' class='bg1'><b>패배</b></td>
        <td style='text-align:center;'>{$general['deathnum']}</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>살상률</b></td>
        <td style='text-align:center;'>{$general['killrate']} %</td>
        <td style='text-align:center;' class='bg1'><b>사살</b></td>
        <td style='text-align:center;'>{$general['killcrew']}</td>
        <td style='text-align:center;' class='bg1'><b>피살</b></td>
        <td style='text-align:center;'>{$general['deathcrew']}</td>
    </tr>
</table>
<table width=498 class='tb_layout bg2'>
    <tr><td style='text-align:center;' colspan=4 class='bg1'><b>숙 련 도</b></td></tr>
    <tr height=16>
        <td width=64 style='text-align:center;' class='bg1'><b>보병</b></td>
        <td width=40>　{$general['dex0_text']}</td>
        <td width=60 align=right>{$general['dex0_short']}&nbsp;</td>
        <td width=330 style='text-align:center;'>".bar($dex0, 16)."</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>궁병</b></td>
        <td>　{$general['dex10_text']}</td>
        <td align=right>{$general['dex10_short']}&nbsp;</td>
        <td style='text-align:center;'>".bar($dex10, 16)."</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>기병</b></td>
        <td>　{$general['dex20_text']}</td>
        <td align=right>{$general['dex20_short']}&nbsp;</td>
        <td style='text-align:center;'>".bar($dex20, 16)."</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>귀병</b></td>
        <td>　{$general['dex30_text']}</td>
        <td align=right>{$general['dex30_short']}&nbsp;</td>
        <td style='text-align:center;'>".bar($dex30, 16)."</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>차병</b></td>
        <td>　{$general['dex40_text']}</td>
        <td align=right>{$general['dex40_short']}&nbsp;</td>
        <td style='text-align:center;'>".bar($dex40, 16)."</td>
    </tr>
</table>";
}

function getOnlineNum() {
    return KVStorage::getStorage(DB::db(), 'game_env')->online;
}

function onlinegen() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $onlinegen = "";
    $generalID = Session::getInstance()->generalID;
    $nationID = DB::db()->queryFirstField('select `nation` from `general` where `no` = %i', $generalID);
    if($nationID === null || Util::toInt($nationID) === 0) {
        $onlinegen = $gameStor->onlinegen;
    } else {
        $onlinegen = DB::db()->queryFirstField('select onlinegen from nation where nation=%i',$nationID);
    }
    return $onlinegen;
}

function nationMsg(General $general) {
    $db = DB::db();
    $msg = $db->queryFirstField(
        'SELECT msg FROM nation WHERE nation = %i',
        $general->getNationID()
    );

    return $msg?:'';
}

function banner() {

    return sprintf(
        '<font size=2>%s %s / %s</font>',
        GameConst::$title,
        VersionGit::$version,
        GameConst::$banner);
}

function addTurn($date, int $turnterm, int $turn=1, bool $withFraction=true) {
    $date = new \DateTime($date);
    $target = $turnterm*$turn;
    $date->add(new \DateInterval("PT{$target}M"));
    if($withFraction){
        return $date->format('Y-m-d H:i:s.u');
    }
    return $date->format('Y-m-d H:i:s');
}

function subTurn($date, int $turnterm, int $turn=1, bool $withFraction=true) {
    $date = new \DateTime($date);
    $target = $turnterm*$turn;
    $date->sub(new \DateInterval("PT{$target}M"));
    if($withFraction){
        return $date->format('Y-m-d H:i:s.u');
    }
    return $date->format('Y-m-d H:i:s');
}

function cutTurn($date, int $turnterm, bool $withFraction=true) {
    $date = new \DateTime($date);
    
    $baseDate = new \DateTime($date->format('Y-m-d'));
    $baseDate->sub(new \DateInterval("P1D"));
    $baseDate->add(new \DateInterval("PT1H"));

    $diffMin = intdiv($date->getTimeStamp() - $baseDate->getTimeStamp(), 60);
    $diffMin -= $diffMin % $turnterm;

    $baseDate->add(new \DateInterval("PT{$diffMin}M"));
    if($withFraction){
        return $baseDate->format('Y-m-d H:i:s.u');
    }
    return $baseDate->format('Y-m-d H:i:s');
    
}

function cutDay($date, int $turnterm, bool $withFraction=true) {
    $date = new \DateTime($date);
    
    $baseDate = new \DateTime($date->format('Y-m-d'));
    $baseDate->sub(new \DateInterval("P1D"));
    $baseDate->add(new \DateInterval("PT1H"));

    $baseGap = 12 * $turnterm;

    $diffMin = intdiv($date->getTimeStamp() - $baseDate->getTimeStamp(), 60);

    $timeAdjust = $diffMin % $baseGap;
    $newMonth = intdiv($timeAdjust, $turnterm) + 1;

    $yearPulled = false;
    if($newMonth > 3){//3월 이후일때는
        $yearPulled = true;
        $diffMin += $baseGap;
    }
    $diffMin -= $timeAdjust;

    $baseDate->add(new \DateInterval("PT{$diffMin}M"));
    if($withFraction){
        $dateTimeString = $baseDate->format('Y-m-d H:i:s.u');
    }
    else{
        $dateTimeString = $baseDate->format('Y-m-d H:i:s');
    }
    
    return [$dateTimeString, $yearPulled, $newMonth];
}

function increaseRefresh($type="", $cnt=1) {
    //FIXME: 로그인, 비로그인 시 처리가 명확하지 않음
    $session = Session::getInstance();
    $userID = $session->userID;
    $generalID = $session->generalID;
    $userGrade = $session->userGrade;

    $date = TimeUtil::now();

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->refresh = $gameStor->refresh+$cnt; //TODO: +로 증가하는 값은 별도로 분리
    $isunited = $gameStor->isunited;

    if($isunited != 2 && $generalID && $userGrade < 6) {
        $db->update('general', [
            'lastrefresh'=>$date,
            'con'=>$db->sqleval('con + %i', $cnt),
            'connect'=>$db->sqleval('connect + %i', $cnt),
            'refcnt'=>$db->sqleval('refcnt + %i', $cnt),
            'refresh'=>$db->sqleval('refresh + %i', $cnt)
        ], 'owner=%i', $userID);
    }

    $date = date('Y_m_d H:i:s');
    $date2 = substr($date, 0, 10);
    $online = getOnlineNum();
    file_put_contents(
        __dir__."/logs/".UniqueConst::$serverID."/_{$date2}_refresh.txt",
        sprintf(
            "%s, %s, %s, %s, %s, %d\n",
            $date,
            $session->userName,
            $session->generalName,
            $session->ip,
            $type,
            $online
        ),
        FILE_APPEND
    );

    $proxy_headers = array(
        'HTTP_VIA',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR_IP',
        'VIA',
        'X_FORWARDED_FOR',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION'
    );

    $str = "";
    foreach($proxy_headers as $x) {
        if(isset($_SERVER[$x])) $str .= "//{$x}:{$_SERVER[$x]}";
    }
    if($str != "") {
        file_put_contents(
            __dir__."/logs/".UniqueConst::$serverID."/_{$date2}_ipcheck.txt",
            sprintf(
                "%s, %s, %s%s\n",
                $session->userName,
                $session->generalName,
                $_SERVER['REMOTE_ADDR'],
                $str
            ),
        FILE_APPEND);
    }
}

function updateTraffic() {
    $online = getOnlineNum();
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $admin = $gameStor->getValues(['year','month','refresh','maxonline','maxrefresh']);

    //최다갱신자
    $user = $db->queryFirstRow('select name,refresh from general order by refresh desc limit 1');

    if($admin['maxrefresh'] < $admin['refresh']) {
        $admin['maxrefresh'] = $admin['refresh'];
    }
    if($admin['maxonline'] < $online) {
        $admin['maxonline'] = $online;
    }
    $gameStor->refresh = 0;
    $gameStor->maxrefresh = $admin['maxrefresh'];
    $gameStor->maxonline = $admin['maxonline'];

    $db->update('general', ['refresh'=>0], true);

    $date = TimeUtil::now();
    //일시|년|월|총갱신|접속자|최다갱신자
    file_put_contents(__dir__."/logs/".UniqueConst::$serverID."/_traffic.txt",
        Json::encode([
            $date,
            $admin['year'],
            $admin['month'],
            $admin['refresh'],
            $online,
            $user['name']."(".$user['refresh'].")"
        ])."\n"
    , FILE_APPEND);
}

function CheckOverhead() {
    //서버정보
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    list($turnterm, $conlimit) = $gameStor->getValuesAsArray(['turnterm', 'conlimit']);

    $con = Util::round(pow($turnterm, 0.6) * 3) * 10;


    if($con != $conlimit){
        $gameStor->conlimit = $con;
    }
}

function isLock() {
    return DB::db()->queryFirstField("SELECT plock from plock limit 1") != 0;
}

function tryLock():bool{
    //NOTE: 게임 로직과 관련한 모든 insert, update 함수들은 lock을 거칠것을 권장함.
    $db = DB::db();

    // 잠금
    $db->update('plock', [
        'plock'=>1,
        'locktime'=>TimeUtil::now(true)
    ], 'plock=0');

    return $db->affectedRows() > 0;
}

function unlock():bool{
    // 풀림
    $db = DB::db();
    $db->update('plock', [
        'plock'=>0
    ], true);

    return $db->affectedRows() > 0;
}

function timeover():bool {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    list($turnterm, $turntime) = $gameStor->getValuesAsArray(['turnterm', 'turntime']);
    $diff = (new \DateTime())->getTimestamp() - (new \DateTime($turntime))->getTimestamp();

    $t = min($turnterm, 5);

    $term = $diff;
    if($term >= $t || $term < 0) { return true; }
    else { return false; }
}

function checkDelay() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    //서버정보
    $now = new \DateTimeImmutable();
    $turntime = new \DateTimeImmutable($gameStor->turntime);
    $timeMinDiff = intdiv($now->getTimestamp() - $turntime->getTimestamp(), 60);
    
    // 1턴이상 갱신 없었으면 서버 지연
    $term = $gameStor->turnterm;
    if($term >= 20){
        $threshold = 1;
    }
    else if($term >= 10){
        $threshold = 3;
    }
    else{
        $threshold = 6;
    }
    //지연 해야할 밀린 턴 횟수
    $iter = intdiv($timeMinDiff, $term);
    if($iter > $threshold) {
        $minute = ($iter - $threshold) * $term;
        $newTurntime = $turntime->add(new \DateInterval("PT{$minute}M"));
        $newNextTurntime = $turntime->add(new \DateInterval("PT{$term}M"));
        $gameStor->turntime = $newTurntime->format('Y-m-d H:i:s');
        $gameStor->starttime = (new \DateTimeImmutable($gameStor->starttime))
            ->add(new \DateInterval("PT{$minute}M"))
            ->format('Y-m-d H:i:s');
        $db->update('general', [
            'turntime'=> $db->sqleval('DATE_ADD(turntime, INTERVAL %i MINUTE)', $minute)
        ], true);
        $db->update('auction', [
            'expire'=> $db->sqleval('DATE_ADD(expire, INTERVAL %i MINUTE)', $minute)
        ], true);
    }
}

function updateOnline() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $nationname = ["재야"];

    //국가별 이름 매핑
    foreach(getAllNationStaticInfo() as $nation) {
        $nationname[$nation['nation']] = $nation['name'];
    }


    //동접수
    $query = "select no,name,nation from general where lastrefresh > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $onlinenum = MYDB_num_rows($result);

	$onnation = array();
	$onnationstr = "";
	
    //국가별 접속중인 장수
    for($i=0; $i < $onlinenum; $i++) {
        $general = MYDB_fetch_array($result);
        if(isset($onnation[$general['nation']])){
            $onnation[$general['nation']] .= $general['name'].', ';
        }else {
            $onnation[$general['nation']] = $general['name'].', ';
        }
    }
	
	//$onnation이 empty라면 굳이 foreach를 수행 할 이유가 없음. 
	if(!empty($onnation)){
	    foreach($onnation as $key => $val) {
	        $onnationstr .= "【{$nationname[$key]}】, ";
	
	        if($key == 0) {
                $gameStor->onlinegen = $onnation[0];
	        } else {
	            $query = "update nation set onlinegen='$onnation[$key]' where nation='$key'";
	            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
	        }
	    }
	}

    //접속중인 국가
    $gameStor->online = $onlinenum;
    $gameStor->onlinenation = $onnationstr;
}

function addAge() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    //나이와 호봉 증가
    $query = "update general set age=age+1,belong=belong+1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    if($admin['year'] >= $admin['startyear']+3) {
        foreach($db->query('SELECT no,name,nation,leader,power,intel from general where specage<=age and special=%s', GameConst::$defaultSpecialDomestic) as $general){
            $special = getSpecial($general['leader'], $general['power'], $general['intel']);
            $specialClass = getGeneralSpecialDomesticClass($special);
            $specialText = $specialClass::$name;
            $db->update('general', [
                'special'=>$special
            ], 'no=%i',$general['no']);

            $josaUl = JosaUtil::pick($specialText, '을');
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>{$specialText}</></b>】{$josaUl} 습득");
            pushGenLog($general, "<C>●</>특기 【<b><L>{$specialText}</></b>】{$josaUl} 익혔습니다!");
        }

        foreach($db->query('SELECT no,name,nation,leader,power,intel,npc,dex0,dex10,dex20,dex30,dex40 from general where spec2age<=age and special2=%s', GameConst::$defaultSpecialWar) as $general){
            $special2 = SpecialityConst::pickSpecialWar($general);
            $specialClass = getGeneralSpecialWarClass($special2);
            $specialText = $specialClass::$name;

            $db->update('general', [
                'special2'=>$special2
            ], 'no=%i',$general['no']);

            $josaUl = JosaUtil::pick($specialText, '을');
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>{$specialText}</></b>】{$josaUl} 습득");
            pushGenLog($general, "<C>●</>특기 【<b><L>{$specialText}</></b>】{$josaUl} 익혔습니다!");
        }
    }
}

function turnDate($curtime) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $admin = $gameStor->getValues(['startyear', 'starttime', 'turnterm', 'year', 'month']);

    $turn = $admin['starttime'];
    $curturn = cutTurn($curtime, $admin['turnterm']);
    $term = $admin['turnterm'];
    
    $num = intdiv((strtotime($curturn) - strtotime($turn)), $term*60);

    $date = $admin['startyear'] * 12;
    $date += $num;
    
    $year = intdiv($date, 12);
    $month = 1 + $date % 12;    

    // 바뀐 경우만 업데이트
    if($admin['month'] != $month || $admin['year'] != $year) {
        $gameStor->year = $year;
        $gameStor->month = $month;
    }

    return [$year, $month];
}


function triggerTournament() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['tournament', 'tnmt_trig']);

    //현재 토너먼트 없고, 자동개시 걸려있을때, 40%확률
    if($admin['tournament'] == 0 && $admin['tnmt_trig'] > 0 && rand() % 100 < 40) {
        $type = rand() % 5; //  0 : 전력전, 1 : 통솔전, 2 : 일기토, 3 : 설전
        //전력전 40%, 통, 일, 설 각 20%
        if($type > 3) { $type = 0; }
        startTournament($admin['tnmt_trig'], $type);
    }
}

function CheckHall($no) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $types = array(
        "experience",
        "dedication",
        "firenum",
        "warnum",
        "killnum",
        "winrate",
        "killcrew",
        "killrate",
        "dex0",
        "dex10",
        "dex20",
        "dex30",
        "dex40",
        "ttrate",
        "tlrate",
        "tprate",
        "tirate",
        "betgold",
        "betwin",
        "betwingold",
        "betrate"
    );

    $general = $db->queryFirstRow('SELECT name,name2,owner,nation,picture,imgsvr,
    experience,dedication,warnum,firenum,killnum,
    killnum/warnum as winrate,killcrew,killcrew/deathcrew as killrate,
    dex0,dex10,dex20,dex30,dex40,
    ttw/(ttw+ttd+ttl) as ttrate, ttw+ttd+ttl as tt,
    tlw/(tlw+tld+tll) as tlrate, tlw+tld+tll as tl,
    tpw/(tpw+tpd+tpl) as tprate, tpw+tpd+tpl as tp,
    tiw/(tiw+tid+til) as tirate, tiw+tid+til as ti,
    betgold, betwin, betwingold, betwingold/betgold as betrate
    from general where no=%i', $no);

    if(!$general){
        return;
    }

    $unitedDate = TimeUtil::now();
    $nation = getNationStaticInfo($general['nation']);

    $serverCnt = $db->queryFirstField('SELECT count(*) FROM ng_games');

    [$scenarioIdx, $scenarioName, $startTime] = $gameStor->getValuesAsArray(['scenario', 'scenario_text', 'starttime']);

    $ownerName = $general['name2'];
    if($general['owner']){
        $ownerName = RootDB::db()->queryFirstField('SELECT name FROM member WHERE no = %i', $general['owner']);
    }

    foreach($types as $idx=>$typeName) {
        

        //승률,살상률인데 10회 미만 전투시 스킵
        if(($typeName === 'winrate' || $typeName === 'killrate') && $general['warnum']<10) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($typeName === 'ttrate' && $general['tt'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($typeName === 'tlrate' && $general['tl'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($typeName === 'tprate' && $general['tp'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($typeName === 'tirate' && $general['ti'] < 50) { continue; }
        //수익률인데 1000미만시 스킵
        if($typeName === 'betrate' && $general['betgold'] < 1000) { continue; }

        if($general[$typeName]<=0){
            continue;
        }

        $aux = [
            'name'=>$general['name'],
            'nationName'=>$nation['name'],
            'bgColor'=>$nation['color'],
            'fgColor'=>newColor($nation['color']),
            'picture'=>$general['picture'],
            'imgsvr'=>$general['imgsvr'],
            'startTime'=>$startTime,
            'unitedTime'=>$unitedDate,
            'owner_name'=>$ownerName,
            'serverID'=>UniqueConst::$serverID,
            'serverIdx'=>$serverCnt,
            'serverName'=>UniqueConst::$serverName,
            'scenarioName'=>$scenarioName,
        ];
        $jsonAux = Json::encode($aux);

        $db->insertIgnore('ng_hall', [
            'server_id'=>UniqueConst::$serverID,
            'scenario'=>$scenarioIdx,
            'general_no'=>$no,
            'type'=>$idx,
            'value'=>$general[$typeName]??0,
            'owner'=>$general['owner']??null,
            'aux'=>$jsonAux
        ]);

        if($db->affectedRows() == 0){
            $db->update('ng_hall', [
                'value'=>$general[$typeName]??0,
                'aux'=>$jsonAux
            ], 
            'server_id = %s AND scenario = %i AND general_no = %i AND type = %i AND value < %d', 
            UniqueConst::$serverID,
            $scenarioIdx,
            $no,
            $idx,
            $general[$typeName]??0);
        }
        
    }
}

function tryUniqueItemLottery(General $general, string $acquireType='아이템'):bool{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    if($general->getVar('npc') >= 2){
        return false;
    }

    if($general->getVar('npc') > 6 || $general->getVar('weap') > 6 || $general->getVar('book') > 6 || $general->getVar('item') > 6){
        return false;
    }

    $scenario = $gameStor->scenario;
    $genCount = $db->queryFirstField('SELECT count(*) FROM general WHERE npc<2');

    if ($scenario < 100) {
        $prob = 1 / ($genCount * 5); // 5~6개월에 하나씩 등장
    }
    else { 
        $prob = 1 / $genCount; // 1~2개월에 하나씩 등장
    }  

    if($acquireType == '투표'){
        $prob = 1 / ($genCount * 0.7 / 3); // 투표율 70%, 투표 한번에 2~3개 등장
    }
    else if($acquireType == '랜덤 임관'){
        $prob = 1 / ($genCount / 10/ 2); // 랜임시 2개(10%) 등장(200명중 20명 랜임시도?)
    }
    else if($acquireType == '건국'){
        $prob = 1 / ($genCount / 10/ 4); // 건국시 4개(20%) 등장(200명시 20국 정도 됨)
    }
    
    $prob = Util::valueFit($prob, 1/3, 1);

    if(!Util::randBool($prob)){
        return false;
    }

    //아이템 습득 상황
    $availableUnique = [];
    $itemTypes = [
        'horse'=>'getHorseName',
        'weap'=>'getWeapName',
        'book'=>'getBookName',
        'item'=>'getItemName'
    ];
    $itemCodeList = range(7, 26); // [7, 26] 20개
    
    foreach($itemTypes as $itemType=>$itemNameFunc){
        foreach($itemCodeList as $itemCode){
            $availableUnique["{$itemType}_{$itemCode}"] = [$itemType, $itemCode];
        }
    }

    //TODO: 너무 바보 같다. 장기적으로는 유니크 아이템 테이블 같은게 필요하지 않을까?
    foreach ($itemTypes as $itemType=>$itemNameFunc) {
        foreach($db->queryFirstColumn('SELECT %b FROM general WHERE %b > 6', $itemType, $itemType) as $itemCode){
            unset($availableUnique["{$itemType}_{$itemCode}"]);
        }
    }

    if(!$availableUnique){
        return false;
    }

    [$itemType, $itemCode] = Util::choiceRandom($availableUnique);
    
    $nationName = $general->getStaticNation()['name'];
    $generalName = $general->getNation();
    $josaYi = JosaUtil::pick($generalName, '이');
    $itemName = ($itemTypes[$itemType])($itemCode);
    $josaUl = JosaUtil::pick($itemName, '을');


    $general->setVar($itemType, $itemCode);

    $logger = $general->getLogger();

    $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 습득했습니다!");
    $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 습득했습니다!");
    $logger->pushGlobalHistoryLog("<C><b>【{$acquireType}】</b></><D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 습득했습니다!");

    return true;
}

function uniqueItem($general, $log, $vote=0) {
    //TODO: uniqueItem 을 쓰는 경우를 모두 제거.
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $alllog = [];
    $history = [];
    $occupied = [];
    $item = [];

    if($general['npc'] >= 2) { return $log; }
    if($general['weap'] > 6 || $general['book'] > 6 || $general['horse'] > 6 || $general['item'] > 6) { return $log; }

    $admin = $gameStor->getValues(['year', 'month', 'scenario']);

    $query = "select count(*) as cnt from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if($admin['scenario'] < 100)  { $prob = $gen['cnt'] * 5; }  // 5~6개월에 하나씩 등장
    else { $prob = $gen['cnt']; }  // 1~2개월에 하나씩 등장

    if($vote == 1) { $prob = Util::round($gen['cnt'] * 0.7 / 3); }     // 투표율 70%, 투표 한번에 2~3개 등장
    elseif($vote == 2) { $prob = Util::round($gen['cnt'] / 10 / 2); }   // 랜임시 2개(10%) 등장(200명중 20명 랜임시도?)
    elseif($vote == 3) { $prob = Util::round($gen['cnt'] / 10 / 4); }   // 건국시 4개(20%) 등장(200명시 20국 정도 됨)

    if($prob < 3) { $prob = 3; }
    //아이템 습득 상황
    if(rand() % $prob == 0) {
        //셋중 선택
        $sel = rand() % 4;
        switch($sel) {
        case 0: $type = "weap"; break;
        case 1: $type = "book"; break;
        case 2: $type = "horse"; break;
        case 3: $type = "item"; break;
        }
        $query = "select no,{$type} from general where {$type}>6";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        if($count < 20) {
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $occupied[$gen[$type]] = 1;
            }
            for($i=7; $i <= 26; $i++) {
                if(!Util::array_get($occupied[$i])) {
                    $item[] = $i;
                }
            }
            $it = $item[rand() % count($item)]??0;

            $query = "update general set {$type}='$it' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation = getNationStaticInfo($general['nation']);

            switch($sel) {
            case 0:
                $josaUl = JosaUtil::pick(getWeapName($it), '을');
                $josaYi = JosaUtil::pick($general['name'], '이');
                $log[] = "<C>●</><C>".getWeapName($it)."</>{$josaUl} 습득했습니다!";
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <C>".getWeapName($it)."</>{$josaUl} 습득했습니다!";
                pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getWeapName($it)."</>{$josaUl} 습득");
                if($vote == 0) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getWeapName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 1) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getWeapName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 2) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getWeapName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 3) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getWeapName($it)."</>{$josaUl} 습득했습니다!";
                }
                break;
            case 1:
                $josaUl = JosaUtil::pick(getBookName($it), '을');
                $josaYi = JosaUtil::pick($general['name'], '이');
                $log[] = "<C>●</><C>".getBookName($it)."</>{$josaUl} 습득했습니다!";
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <C>".getBookName($it)."</>{$josaUl} 습득했습니다!";
                pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getBookName($it)."</>{$josaUl} 습득");
                if($vote == 0) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getBookName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 1) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getBookName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 2) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getBookName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 3) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getBookName($it)."</>{$josaUl} 습득했습니다!";
                }
                break;
            case 2:
                $josaUl = JosaUtil::pick(getHorseName($it), '을');
                $josaYi = JosaUtil::pick($general['name'], '이');
                $log[] = "<C>●</><C>".getHorseName($it)."</>{$josaUl} 습득했습니다!";
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <C>".getHorseName($it)."</>{$josaUl} 습득했습니다!";
                pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getHorseName($it)."</>{$josaUl} 습득");
                if($vote == 0) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getHorseName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 1) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getHorseName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 2) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getHorseName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 3) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getHorseName($it)."</>{$josaUl} 습득했습니다!";
                }
                break;
            case 3:
                $josaUl = JosaUtil::pick(getItemName($it), '을');
                $josaYi = JosaUtil::pick($general['name'], '이');
                $log[] = "<C>●</><C>".getItemName($it)."</>{$josaUl} 습득했습니다!";
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <C>".getItemName($it)."</>{$josaUl} 습득했습니다!";
                pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getItemName($it)."</>{$josaUl} 습득");
                if($vote == 0) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getItemName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 1) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getItemName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 2) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getItemName($it)."</>{$josaUl} 습득했습니다!";
                } elseif($vote == 3) {
                    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} <C>".getItemName($it)."</>{$josaUl} 습득했습니다!";
                }
                break;
            }
            pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
            pushWorldHistory($history, $admin['year'], $admin['month']);
        }
    }
    return $log;
}

function checkAbilityEx(int $generalID, ActionLogger $actLog){
    $db = DB::db();
    $general = $db->queryFirstRow('SELECT leader,leader2,power,power2,intel,intel2 FROM general WHERE no = %i',$generalID);

    if(!$general){
        return;
    }

    if($general['leader2'] < 0){
        $db->update('general',[
            'leader2'=>$db->sqleval('leader2 + %i', GameConst::$upgradeLimit),
            'leader'=>$db->sqleval('leader - 1')
        ], 'no=%i', $generalID);
        $actLog->pushGeneralActionLog('<R>통솔</>이 <C>1</> 떨어졌습니다!', ActionLogger::PLAIN);
        return;
    }

    if($general['power2'] < 0){
        $db->update('general',[
            'power2'=>$db->sqleval('power2 + %i', GameConst::$upgradeLimit),
            'power'=>$db->sqleval('power - 1')
        ], 'no=%i', $generalID);
        $actLog->pushGeneralActionLog('<R>무력</>이 <C>1</> 떨어졌습니다!', ActionLogger::PLAIN);
        return;
    }

    if($general['intel2'] < 0){
        $db->update('general',[
            'intel2'=>$db->sqleval('intel2 + %i', GameConst::$upgradeLimit),
            'intel'=>$db->sqleval('intel - 1')
        ], 'no=%i', $generalID);
        $actLog->pushGeneralActionLog('<R>지력</>이 <C>1</> 떨어졌습니다!', ActionLogger::PLAIN);
        return;
    }

    if($general['leader2'] >= GameConst::$upgradeLimit){
        $db->update('general',[
            'leader2'=>$db->sqleval('leader2 - %i', GameConst::$upgradeLimit),
            'leader'=>$db->sqleval('leader + 1')
        ], 'no=%i', $generalID);
        $actLog->pushGeneralActionLog('<S>통솔</>이 <C>1</> 올랐습니다!', ActionLogger::PLAIN);
        return;
    }

    if($general['power2'] >= GameConst::$upgradeLimit){
        $db->update('general',[
            'power2'=>$db->sqleval('power2 - %i', GameConst::$upgradeLimit),
            'power'=>$db->sqleval('power + 1')
        ], 'no=%i', $generalID);
        $actLog->pushGeneralActionLog('<S>무력</>이 <C>1</> 올랐습니다!', ActionLogger::PLAIN);
        return;
    }

    if($general['intel2'] >= GameConst::$upgradeLimit){
        $db->update('general',[
            'intel2'=>$db->sqleval('intel2 - %i', GameConst::$upgradeLimit),
            'intel'=>$db->sqleval('intel + 1')
        ], 'no=%i', $generalID);
        $actLog->pushGeneralActionLog('<S>지력</>이 <C>1</> 올랐습니다!', ActionLogger::PLAIN);
        return;
    }
}

function checkAbility($general, $log) {
    $db = DB::db();
    $connect=$db->get();

    $limit = GameConst::$upgradeLimit;

    $query = "select no,leader,leader2,power,power2,intel,intel2 from general where no='{$general['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($general['leader2'] < 0) {
        $query = "update general set leader2='$limit'+leader2,leader=leader-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><R>통솔</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['leader2'] >= $limit) {
        $query = "update general set leader2=leader2-'$limit',leader=leader+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><S>통솔</>이 <C>1</> 올랐습니다!";
    }

    if($general['power2'] < 0) {
        $query = "update general set power2='$limit'+power2,power=power-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><R>무력</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['power2'] >= $limit) {
        $query = "update general set power2=power2-'$limit',power=power+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><S>무력</>이 <C>1</> 올랐습니다!";
    }

    if($general['intel2'] < 0) {
        $query = "update general set intel2='$limit'+intel2,intel=intel-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><R>지력</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['intel2'] >= $limit) {
        $query = "update general set intel2=intel2-'$limit',intel=intel+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><S>지력</>이 <C>1</> 올랐습니다!";
    }

    return $log;
}

function checkDedication($general, $log) {
    $db = DB::db();
    $connect=$db->get();

    $dedlevel = getDedLevel($general['dedication']);

    $query = "update general set dedlevel='$dedlevel' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 승급했다면
    $josaRoDed = JosaUtil::pick(getDed($general['dedication']), '로');
    $josaRoBill = JosaUtil::pick(getBill($general['dedication']), '로');
    if($general['dedlevel'] < $dedlevel) {
        $log[] = "<C>●</><Y>".getDed($general['dedication'])."</>{$josaRoDed} <C>승급</>하여 봉록이 <C>".getBill($general['dedication'])."</>{$josaRoBill} <C>상승</>했습니다!";
    // 강등했다면
    } elseif($general['dedlevel'] > $dedlevel) {
        $log[] = "<C>●</><Y>".getDed($general['dedication'])."</>{$josaRoDed} <R>강등</>되어 봉록이 <C>".getBill($general['dedication'])."</>{$josaRoBill} <R>하락</>했습니다!";
    }

    return $log;
}

function checkExperience($general, $log) {
    $db = DB::db();
    $connect=$db->get();

    $explevel = getExpLevel($general['experience']);

    $query = "update general set explevel='$explevel' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 승급했다면
    if($general['explevel'] < $explevel) {
        $log[] = "<C>●</><C>Lv $explevel</>로 <C>레벨업</>!";
    // 강등했다면
    } elseif($general['explevel'] > $explevel) {
        $log[] = "<C>●</><C>Lv $explevel</>로 <R>레벨다운</>!";
    }

    return $log;
}

function getAdmin() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    return $gameStor->getAll();
}

function getMe() {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $query = "select * from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error("접속자가 많아 접속을 중단합니다. 잠시후 갱신해주세요.<br>getMe : ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    return $me;
}

function getTroop($troop) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select * from troop where troop='$troop'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $troop = MYDB_fetch_array($result);

    return $troop;
}

function getCity($city, $sel="*") {
    $db = DB::db();
    $connect=$db->get();

    $query = "select {$sel} from city where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    return $city;
}

function getNation($nation) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select * from nation where nation='$nation'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    return $nation;
}

function deleteNation(General $general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
    $nation = $general->getStaticNation();
    $nationName = $nation['name'];
    $nationID = $nation['nation'];

    $logger = $general->getLogger();

    $josaUn = JosaUtil::pick($nationName, '은');
    $logger->pushGlobalHistoryLog("<R><b>【멸망】</b></><D><b>{$nationName}</b></>{$josaUn} <R>멸망</>했습니다.");

    $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $nationID);
    $oldNationGenerals = $db->queryFirstColumn('SELECT `no` FROM general WHERE nation=%i', $nationID);
    $oldNation['generals'] = $oldNationGenerals;
    $oldNation['aux'] = Json::decode($oldNation['aux']);

    $general->setVar('belong', 0);
    $general->setVar('troop', 0);
    $general->setVar('level', 0);
    $general->setVar('nation', 0);
    $general->setVar('makelimit', 12);

    // 전 장수 재야로    // 전 장수 소속 무소속으로
    $db->update('general', [
        'belong'=>0,
        'troop'=>0,
        'level'=>0,
        'nation'=>0,
        'makelimit'=>12,
        'permission'=>'normal',
    ], 'nation=%i', $nationID);
    // 도시 공백지로
    $db->update('city', [
        'nation'=>0,
        'front'=>0,
        'gen1'=>0,
        'gen2'=>0,
        'gen3'=>0,
    ], 'nation=%i', $nationID);
    // 부대 삭제
    $db->delete('troop', 'nation=%i', $nationID);
    // 국가 삭제

    $db->insert('ng_old_nations', [
        'server_id'=>UniqueConst::$serverID,
        'nation'=>$nationID,
        'data'=>Json::encode($oldNation)
    ]);
    $db->delete('nation', 'nation=%i', $nationID);
    $db->delete('nation_turn', 'nation_id=%i', $nationID);
    // 외교 삭제
    $db->delete('diplomacy', 'me = %i OR you = %i', $nationID, $nationID);

    refreshNationStaticInfo();
}

function nextRuler(General $general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    
    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
    $nation = $general->getStaticNation();
    $nationName = $nation['name'];
    $nationID = $nation['nation'];

    $candidate = null;

    //npc or npc유저인 경우 후계 찾기
    if($general->getVar('npc') > 0) {
        $candidate = $db->queryFirstRow(
            'SELECT no,name,nation,IF(ABS(affinity-%i)>75,150-ABS(affinity-%i),ABS(affinity-%i)) as npcmatch2 from general where nation=%i and level!=12 and npc>0 order by npcmatch2,rand() LIMIT 1',
            $general->getVar('affinity'),
            $general->getVar('affinity'),
            $nationID
        );
    }
    if(!$candidate){
        $candidate = $db->queryFirstRow(
            'SELECT no,name,npc FROM general WHERE nation=%i and level!= 12 AND level >= 9 ORDER BY level DESC LIMIT 1',
            $nationID
        );
    }
    if(!$candidate){
        $candidate = $db->queryFirstRow(
            'SELECT no,name,npc FROM general WHERE nation=%i and level!= 12 ORDER BY dedication DESC LIMIT 1',
            $nationID
        );
    }


    if(!$candidate){
        DeleteConflict($general['nation']);
        deleteNation($general);
        return;
    }

    $nextRulerID = $candidate['no'];
    $nextRulerName = $candidate['name'];

    $general->setVar('level', 1);

    $db->update('general', [
        'level'=>12
    ], 'no=%i', $nextRulerID);
    $db->update('city', [
        'gen1'=>0
    ], 'gen1=%i', $nextRulerID);
    $db->update('city', [
        'gen2'=>0
    ], 'gen2=%i', $nextRulerID);
    $db->update('city', [
        'gen3'=>0
    ], 'gen3=%i', $nextRulerID);

    $josaYi = JosaUtil::pick($nextRulerName, '이');

    $logger = $general->getLogger();
    $logger->pushGlobalHistoryLog("<C><b>【유지】</b></><Y>{$nextRulerName}</>{$josaYi} <D><b>{$nationName}</b></>의 유지를 이어 받았습니다");
    $logger->pushGlobalHistoryLog("<C><b>【유지】</b></><Y>{$nextRulerName}</>{$josaYi} <D><b>{$nationName}</b></>의 유지를 이어 받음.");
    // 장수 삭제 및 부대처리는 checkTurn에서
}

/**
 * $maxDist 이내의 도시를 검색하는 함수
 * @param $from 기준 도시 코드
 * @param $maxDist 검색하고자 하는 최대 거리
 * @param $distForm 리턴 타입. true일 경우 $result[$dist] = [...$city] 이며, false일 경우 $result[$city] = $dist 임
 */
function searchDistance(int $from, int $maxDist=99, bool $distForm = false) {
    $queue = new \SplQueue();

    $cities = [];
    $distanceList = [];

    $queue->enqueue([$from, 0]);

    while(!$queue->isEmpty()){
        list($cityID, $dist) = $queue->dequeue();
        if(key_exists($cityID, $cities)){
            continue;
        }

        if(!key_exists($dist, $distanceList)){
            $distanceList[$dist] = [];
        }
        $distanceList[$dist][] = $cityID;

        $cities[$cityID] = $dist;
        if($dist >= $maxDist){
            continue;
        }

        foreach(array_keys(CityConst::byID($cityID)->path) as $connCityID){
            if(key_exists($connCityID, $cities)){
                continue;
            }
            $queue->enqueue([$connCityID, $dist+1]);
        }
    }

    if($distForm){
        unset($distanceList[0]);
        return $distanceList;
    }
    else{
        return $cities;
    }
}

function isNeighbor(int $nation1, int $nation2, bool $includeNoSupply=true) {
    if($nation1 === $nation2){
        return false;
    }
    $db = DB::db();

    $nation1Cities = [];

    if($includeNoSupply){
        $supplySql = '';
    }
    else{
        $supplySql = 'AND supply = 1';
    }

    foreach($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i %l', $nation1, $supplySql) as $city){
        $nation1Cities[$city] = $city;
    }

    foreach($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i %l', $nation2, $supplySql) as $city){
        foreach(array_keys(CityConst::byID($city)->path) as $adjCity){
            if(key_exists($adjCity, $nation1Cities)){
                return true;
            }
        }
    }

    return false;
}

function CharExperience($exp, $personal) {
    switch($personal) {
        case  0:    case  1;    case  6:
            $exp *= 1.1; break;
        case  4:    case  5:    case  7:    case 10:
            $exp *= 0.9; break;
    }
    $exp = Util::round($exp);

    return $exp;
}

function getCharExpMultiplier($personal):float{
    static $table = [
        0 => 1.1,
        1 => 1.1,
        4 => 0.9,
        5 => 0.9,
        6 => 1.1,
        7 => 0.9,
        10 => 0.9
    ];
    return $table[$personal] ?? 1;
}

function getNationTechMultiplier($nationType):float{
    static $table = [
        3 => 1.1,
        5 => 0.9,
        6 => 0.9,
        7 => 0.9,
        8 => 0.9,
        12 => 0.9,
        13 => 1.1,
    ];
    return $table[$nationType] ?? 1;
}

function getNationWallMultiplier($nationType):float{
    static $table = [
        3 => 1.1,
        5 => 1.1,
        10 => 1.1,
        11 => 1.1,
        4 => 0.9,
        7 => 0.9,
        8 => 0.9,
        13 => 0.9,
    ];
    return $table[$nationType] ?? 1;
}

function getNationPeopleMultiplier($nationType):float{
    static $table = [
        1 => 0.9,
        2 => 1.1,
        3 => 0.9,
        4 => 1.1,
        7 => 1.1,
        9 => 0.9,
        10 => 1.1,
    ];
    return $table[$nationType] ?? 1;
}


function CharDedication($ded, $personal) {
    switch($personal) {
        case 10:
            $ded *= 0.9; break;
    }
    $ded = Util::round($ded);

    return $ded;
}

function getCharDedMultiplier($personal):float{
    static $table = [
        10 => 0.9
    ];
    return $table[$personal] ?? 1;
}

function CharAtmos($atmos, $personal) {
    switch($personal) {
        case  2:    case  4:
            $atmos += 5; break;
        case  0:    case  9:    case 10:
            $atmos -= 5; break;
    }

    return $atmos;
}

function CharTrain($train, $personal) {
    switch($personal) {
        case  3:    case  5:
            $train += 5; break;
        case  1:    case  8:    case 10:
            $train -= 5; break;
    }

    return $train;
}

function CharCost($cost, $personal) {
    switch($personal) {
        case  7:    case  8:    case 9:
            $cost *= 0.8; break;
        case  2:    case  3:    case 6:
            $cost *= 1.2; break;
    }

    return $cost;
}

function CharCritical($rate, $personal) {
    switch($personal) {
        case 10:
            $rate += 10; break;
    }

    return $rate;
}

function SabotageInjuryEx(array $cityGeneralList, bool $isSabotage):int{
    $injuryCount = 0;
    if($isSabotage){
        $text = '<M>계략</>으로 인해 <R>부상</>을 당했습니다.';
    }
    else{
        $text = '<M>재난</>으로 인해 <R>부상</>을 당했습니다.';
    }

    $db = DB::db();

    foreach($cityGeneralList as $general){
        /** @var General $general */
        if(!Util::randBool(0.3)){
            continue;
        }
        $general->getLogger()->pushGeneralActionLog($text);

        $general->increaseVarWithLimit('injury', Util::randRangeInt(1, 16), 0, 80);
        $general->multiplyVar('crew', 0.98);
        $general->multiplyVar('atmos', 0.98);
        $general->multiplyVar('train', 0.98);
        
        $general->applyDB($db);

        $injuryCount += 1;
    }

    return $injuryCount;
}

function SabotageInjury($city, $type=0) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $log = [];

    $injuryCount = 0;

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select no,name,nation from general where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    if($type == 0) {
        $log[0] = "<C>●</>{$admin['month']}월:<M>계략</>으로 인해 <R>부상</>을 당했습니다.";
    } else {
        $log[0] = "<C>●</>{$admin['month']}월:<M>재난</>으로 인해 <R>부상</>을 당했습니다.";
    }
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);

        $injury = rand() % 100;
        if($injury < 30) {  // 부상률 30%
            $injury = intdiv($injury, 2) + 1;   // 부상 1~16

            $query = "update general set crew=crew*0.98,atmos=atmos*0.98,train=train*0.98,injury=injury+'$injury' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($general, $log);
            $injuryCount += 1;
        }
    }

    return $injuryCount;
}

function getRandTurn($term) {
    $randSecond = Util::randRangeInt(0, 60 * $term - 1);
    $randFraction = Util::randRangeInt(0, 999999) / 1000000;//6자리 소수

    return TimeUtil::nowAddSeconds($randSecond + $randFraction, true);
}

function getRandTurn2($term) {
    $randSecond = Util::randRangeInt(0, 60 * $term - 1);
    $randFraction = Util::randRangeInt(0, 999999) / 1000000;//6자리 소수
    
    return TimeUtil::nowAddSeconds(- $randSecond - $randFraction, true);
}
