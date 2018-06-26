<?php
namespace sammo;

require_once 'process_war.php';
require_once 'func_gamerule.php';
require_once 'func_process.php';
require_once 'func_process_sabotage.php';
require_once 'func_process_chief.php';
require_once 'func_process_personnel.php';
require_once 'func_npc.php';
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
    static $freeNation = [
        'nation'=>0,
        'name'=>'재야',
        'color'=>'#000000',
        'type'=>0,
        'level'=>0,
        'capital'=>0
    ];

    if ($forceRefresh) {
        $nationList = null;
    }

    if ($nationID === null) {
       return null;
    }
    if($nationID === 0){
        return $freeNation;
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
    $rate = $city['rate'];
    $agri = $city['agri'] / $city['agri2'] * 100;
    $comm = $city['comm'] / $city['comm2'] * 100;
    $secu = $city['secu'] / $city['secu2'] * 100;
    $def  = $city['def'] / $city['def2'] * 100;
    $wall = $city['wall'] / $city['wall2'] * 100;
    if($city['trade'] == 0) {
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
        <td height=7>".bar($rate)."</td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>태수</b></td>
        <td rowspan=2 style='text-align:center;'>{$gen1['name']}</td>
    </tr>
    <tr>
        <td colspan=3 style='text-align:center;'>{$city['pop']}/{$city['pop2']}</td>
        <td style='text-align:center;'>{$city['rate']}</td>
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
        <td rowspan=2 style='text-align:center;' class='bg1'><b>시중</b></td>
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

    $query = "select nation,name,color,power,msg,gold,rice,bill,rate,scout,war,sabotagelimit,surlimit,tech,totaltech,level,type from nation where nation='{$me['nation']}'";
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
        <td class='bg1 center'><b>지급율</b></td>
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

    if(TechLimit($admin['startyear'], $admin['year'], $nation['tech'])) { $nation['tech'] = "<font color=magenta>{$nation['tech']}</font>"; }
    else { $nation['tech'] = "<font color=limegreen>{$nation['tech']}</font>"; }

    $nation['tech'] = "$techCall / {$nation['tech']}";
    
    if($me['nation']==0){
        $nation['sabotagelimit'] = "<font color=white>해당 없음</font>";
        $nation['surlimit'] = "<font color=white>해당 없음</font>";
        $nation['scout'] = "<font color=white>해당 없음</font>";
        $nation['war'] = "<font color=white>해당 없음</font>";
        $nation['power'] = "<font color=white>해당 없음</font>";
    } else {
        if($nation['sabotagelimit'] != 0) { $nation['sabotagelimit'] = "<font color=red>{$nation['sabotagelimit']}턴</font>"; }
        else { $nation['sabotagelimit'] = "<font color=limegreen>가 능</font>"; }
    
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
        <td style='text-align:center;'>{$nation['sabotagelimit']}</td>
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

function addCommand($typename, $value, $valid = 1, $color=0) {
    if($valid == 1) {
        switch($color) {
            case 0:
                echo "
    <option style=color:white;background-color:black; value={$value}>{$typename}</option>";
                break;
            case 1:
                echo "
    <option style=color:skyblue;background-color:black; value={$value}>{$typename}</option>";
                break;
            case 2:
                echo "
    <option style=color:orange;background-color:black; value={$value}>{$typename}</option>";
                break;
        }
    } else {
        echo "
    <option style=color:white;background-color:red; value={$value}>{$typename}</option>";
    }
}

function commandGroup($typename, $type=0) {
    if($type == 0) {
        echo "
    <optgroup label='{$typename}' style=color:skyblue;background-color:black;>";
    } else {
        echo "
    </optgroup>";
    }
}

function commandTable() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $userID = Session::getUserID();

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost', 'scenario']);

    $query = "select no,npc,troop,city,nation,level,crew,makelimit,special from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $troop = getTroop($me['troop']);
    $city = getCity($me['city']);

    $nationcount = count(getAllNationStaticInfo());

    $query = "select city from city where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $query = "select no from general where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    $nation = getNationStaticInfo($me['nation']);

    $develcost = $admin['develcost'];
    $develcostA = $admin['develcost'];    $colorA = 0;
    $develcostB = $admin['develcost'];    $colorB = 0;
    $develcostC = $admin['develcost'];    $colorC = 0;
    $develcostD = $admin['develcost'];    $colorD = 0;
    $develcostE = $admin['develcost']*2;  $colorE = 0;
    $develcost3 = $admin['develcost']*3;
    $develcost5 = $admin['develcost']*5;

    // 농상 국가보정
    if($nation['type'] == 2 || $nation['type'] == 12)                                             { $develcostA *= 0.8;   $colorA = 1; }
    if($nation['type'] == 8 || $nation['type'] == 11)                                                                   { $develcostA *= 1.2;   $colorA = 2; }
    // 기술 국가보정
    if($nation['type'] == 3 || $nation['type'] == 13)                                                                   { $develcostB *= 0.8;   $colorB = 1; }
    if($nation['type'] == 5 || $nation['type'] == 6 || $nation['type'] == 7 || $nation['type'] == 8 || $nation['type'] == 12) { $develcostB *= 1.2;   $colorB = 2; }
    // 수성 국가보정
    if($nation['type'] == 3 || $nation['type'] == 5 || $nation['type'] == 10 || $nation['type'] == 11)                      { $develcostC *= 0.8;   $colorC = 1; }
    if($nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 8  || $nation['type'] == 13)                      { $develcostC *= 1.2;   $colorC = 2; }
    // 치안 국가보정
    if($nation['type'] == 1 || $nation['type'] == 4)                                                                    { $develcostD *= 0.8;   $colorD = 1; }
    if($nation['type'] == 6 || $nation['type'] == 9)                                                                    { $develcostD *= 1.2;   $colorD = 2; }
    // 민심,정착장려 국가보정
    if($nation['type'] == 2 || $nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 10) { $develcostE *= 0.8;   $colorE = 1; }
    if($nation['type'] == 1 || $nation['type'] == 3 || $nation['type'] == 9)                                                                    { $develcostE *= 1.2;   $colorE = 2; }

    $develcostA = Util::round($develcostA);
    $develcostB = Util::round($develcostB);
    $develcostC = Util::round($develcostC);
    $develcostD = Util::round($develcostD);
    $develcostE = Util::round($develcostE);

    echo "
<select name=commandtype size=1 style=width:260px;color:white;background-color:black;font-size:12px;>";
    addCommand("휴 식", 0);
    addCommand("요 양", 50);
    commandGroup("========= 내 정 ==========");
    if($me['level'] >= 1 && ($citycount != 0 || $admin['year'] >= $admin['startyear']+3) && $city['supply'] != 0) {
        addCommand("농지개간(지력경험, 자금$develcostA)", 1, 1, $colorA);
        addCommand("상업투자(지력경험, 자금$develcostA)", 2, 1, $colorA);
        addCommand("기술연구(지력경험, 자금$develcostB)", 3, 1, $colorB);
        addCommand("수비강화(무력경험, 자금$develcostC)", 5, 1, $colorC);
        addCommand("성벽보수(무력경험, 자금$develcostC)", 6, 1, $colorC);
        addCommand("치안강화(무력경험, 자금$develcostD)", 8, 1, $colorD);
        addCommand("정착장려(통솔경험, 군량$develcostE)", 7, 1, $colorE);
        addCommand("주민선정(통솔경험, 군량$develcostE)", 4, 1, $colorE);
    } else {
        addCommand("농지개간(지력경험, 자금$develcostA)", 1, 0);
        addCommand("상업투자(지력경험, 자금$develcostA)", 2, 0);
        addCommand("기술연구(지력경험, 자금$develcostB)", 3, 0);
        addCommand("수비강화(무력경험, 자금$develcostC)", 5, 0);
        addCommand("성벽보수(무력경험, 자금$develcostC)", 6, 0);
        addCommand("치안강화(무력경험, 자금$develcostD)", 8, 0);
        addCommand("정착장려(통솔경험, 군량$develcostE)", 7, 0);
        addCommand("주민선정(통솔경험, 군량$develcostE)", 4, 0);
    }
    if($me['level'] >= 1 && (($nation['level'] > 0 && $city['nation'] == $me['nation'] && $city['supply'] != 0) || $nation['level'] == 0)) {
        addCommand("물자조달(랜덤경험)", 9, 1);
    } else {
        addCommand("물자조달(랜덤경험)", 9, 0);
    }
    commandGroup("", 1);
    commandGroup("========= 군 사 ==========");
    if($me['level'] >= 1 && $citycount > 0) {
        addCommand("첩보(통솔경험, 자금$develcost3, 군량$develcost3)", 31);
        addCommand("징병(통솔경험)", 11);
        addCommand("모병(통솔경험, 자금x2)", 12);
        addCommand("훈련(통솔경험, 사기↓)", 13);
        addCommand("사기진작(통솔경험, 자금↓)", 14);
        //addCommand("전투태세/3턴(통솔경험, 자금↓)", 15);
        addCommand("출병", 16);
    } else {
        addCommand("첩보(통솔경험, 자금$develcost3, 군량$develcost3)", 31, 0);
        addCommand("징병(통솔경험)", 11, 0);
        addCommand("모병(통솔경험, 자금x2)", 12, 0);
        addCommand("훈련(통솔경험, 사기↓)", 13, 0);
        addCommand("사기진작(통솔경험, 자금↓)", 14, 0);
        //addCommand("전투태세/3턴(통솔경험, 자금↓)", 15, 0);
        addCommand("출병", 16, 0);
    }
    if($me['crew'] > 0) {
        addCommand("소집해제(병사↓, 주민↑)", 17);
    } else {
        addCommand("소집해제(병사↓, 주민↑)", 17, 0);
    }

    commandGroup("", 1);
    commandGroup("========= 인 사 ==========");
    addCommand("이동(통솔경험, 자금$develcost, 사기↓)", 21);
    addCommand("강행(통솔경험, 자금$develcost5, 병력/사기/훈련↓)", 30);
    
    if($nation['level'] > 0 && $me['level'] >= 1) {
        addCommand("인재탐색(랜덤경험, 자금$develcost)", 29);
    } else {
        addCommand("인재탐색(랜덤경험, 자금$develcost)", 29, 0);
    }

    if($me['level'] >= 1 && $city['supply'] != 0) {
        addCommand("등용(자금{$develcost5}+장수가치)", 22);
    } else {
        addCommand("등용(자금{$develcost5}+장수가치)", 22, 0);
    }
    
    if($me['no'] == $troop['no'] && $citycount > 0 && $city['supply'] != 0 && $city['nation'] == $me['nation']) {
        addCommand("집합(통솔경험)", 26);
    } else {
        addCommand("집합(통솔경험)", 26, 0);
    }
    if($me['level'] >= 1 && $me['level'] <= 12 && $nation['level'] > 0) {
        addCommand("귀환(통솔경험)", 28);
    } else {
        addCommand("귀환(통솔경험)", 28, 0);
    }
    if($me['level'] == 0 && $nationcount != 0 && $me['makelimit'] == 0) {
        addCommand("임관", 25);
    } else {
        addCommand("임관", 25, 0);
    }
    commandGroup("", 1);
    commandGroup("========= 계 략 ==========");
    if($me['level'] >= 1 && (($nation['level'] > 0 && $city['nation'] == $me['nation'] && $city['supply'] != 0) || $nation['level'] == 0)) {
        addCommand("화계(지력경험, 자금$develcost5, 군량$develcost5)", 32);
        addCommand("탈취(무력경험, 자금$develcost5, 군량$develcost5)", 33);
        addCommand("파괴(무력경험, 자금$develcost5, 군량$develcost5)", 34);
        addCommand("선동(통솔경험, 자금$develcost5, 군량$develcost5)", 35);
    } else {
        addCommand("화계(지력경험, 자금$develcost5, 군량$develcost5)", 32, 0);
        addCommand("탈취(무력경험, 자금$develcost5, 군량$develcost5)", 33, 0);
        addCommand("파괴(무력경험, 자금$develcost5, 군량$develcost5)", 34, 0);
        addCommand("선동(통솔경험, 자금$develcost5, 군량$develcost5)", 35, 0);
    }
    commandGroup("", 1);
    commandGroup("========= 개 인 ==========");
    if($me['level'] >= 1) {
        addCommand("단련(자금$develcost, 군량$develcost)", 41);
    } else {
        addCommand("단련(자금$develcost, 군량$develcost)", 41, 0);
    }
    addCommand("견문(자금?, 군량?, 경험치?)", 42);
    if($city['trade'] > 0 || $me['special'] == 30) {
        addCommand("장비매매", 48);
        addCommand("군량매매", 49);
    } else {
        addCommand("장비매매", 48, 0);
        addCommand("군량매매", 49, 0);
    }
    if($city['supply'] != 0 && $city['nation'] == $me['nation']) {
        addCommand("증여(통솔경험)", 43);
    } else {
        addCommand("증여(통솔경험)", 43, 0);
    }

    if($me['level'] >= 1 && $city['supply'] != 0 && $city['nation'] == $me['nation']) {
        addCommand("헌납(통솔경험)", 44);
    } else {
        addCommand("헌납(통솔경험)", 44, 0);
    }
    if($me['npc'] == 0) {
        if($me['level'] >= 1 && $me['level'] < 12) {
            addCommand("하야", 45);
        } else {
            addCommand("하야", 45, 0);
        }
    }
    if($me['level'] == 0) {
        addCommand("거병", 55);
    } else {
        addCommand("거병", 55, 0);
    }
    if($me['level'] == 12 &&
        ($city['level'] == 5 || $city['level'] == 6) &&
        $city['nation'] == 0 &&
        $me['makelimit'] == 0 &&
        $gencount >= 2 &&
        $citycount == 0 &&
        $admin['year'] < $admin['startyear']+2
    ) {
        addCommand("건국", 46);
    } else {
        addCommand("건국", 46, 0);
    }
    if($me['level'] == 12) {
        addCommand("선양", 54);
        if($citycount != 0) {
            if ($admin['year'] + 3 < $admin['startyear']) {
                addCommand("방랑", 47, 0);
            }
            else{
                addCommand("방랑", 47);
            }
            
            addCommand("해산", 56, 0);
        } else {
            addCommand("방랑", 47, 0);
            addCommand("해산", 56);
        }
    } else {
        addCommand("선양", 54, 0);
        addCommand("방랑", 47, 0);
        addCommand("해산", 56, 0);
    }
    if($me['level'] > 1 && $me['level'] < 12) {
        addCommand("모반시도", 57);
    } else {
        addCommand("모반시도", 57, 0);
    }
    commandGroup("", 1);

    echo "
</select>
";
}

function CoreCommandTable() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $userID = Session::getUserID();

    $develcost = $gameStor->develcost;

    $query = "select no,nation,city,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select level,can_change_flag from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);

    $query = "select supply from city where city='{$me['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($nation['level'] > 0) { $valid = 1; }
    else { $valid = 0; }
    if($city['supply'] == 0) { $valid = 0; }

    echo "
<select name=commandtype size=1 style=color:white;background-color:black;font-size:13>";
    addCommand("휴 식", 99);
    commandGroup("", 1);
    commandGroup("====== 인 사 ======");
    addCommand("발령", 27, $valid);
    addCommand("포상", 23, $valid);
    addCommand("몰수", 24, $valid);
    commandGroup("", 1);
    commandGroup("====== 외 교 ======");
    addCommand("통합 제의", 53, $valid);

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

function myInfo() {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();
    
    $query = "select no from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    generalInfo($me['no']);
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
    $itemname = getItemName($general['item']);
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

    if($general['age'] < 60)     { $general['age'] = "<font color=limegreen>{$general['age']} 세</font>"; }
    elseif($general['age'] < 80) { $general['age'] = "<font color=yellow>{$general['age']} 세</font>"; }
    else                  { $general['age'] = "<font color=red>{$general['age']} 세</font>"; }

    $general['connect'] = Util::round($general['connect'] / 10) * 10;
    $special = $general['special'] == 0 ? "{$general['specage']}세" : "<font color=limegreen>".getGenSpecial($general['special'])."</font>";
    $special2 = $general['special2'] == 0 ? "{$general['specage2']}세" : "<font color=limegreen>".getGenSpecial($general['special2'])."</font>";

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
        <td width=64 height=64 rowspan=3 style='text-align:center;background:no-repeat center url(\"{$imageTemp}/{$general['picture']}\");background-size:64px;'>&nbsp;</td>
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
        <td style='text-align:center;' colspan=2>".getGenChar($general['personal'])."</td>
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

function adminMsg() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $msg = $gameStor->msg;

    return "운영자 메세지 : <span style='color:yellow;'>$msg</span>";
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

function onlineNation() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    return $gameStor->onlinenation;
}

function nationMsg() {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $msg = $db->queryFirstField(
        'SELECT msg FROM nation WHERE nation = (SELECT nation FROM general WHERE `owner` = %i)',
        $userID
    );

    return $msg?:'';
}

function msgprint($msg, $name, $picture, $imgsvr, $when, $num, $type) {
    $db = DB::db();
    $connect=$db->get();

    $message = explode("|", $msg);
    $count = (count($message) - 2)/2;
    $message[0] = Tag2Code($message[0]);
    $message[1] = Tag2Code($message[1]);
//    $message[0] = str_replace("\n", "<br>", $message[0]);
//    $message[1] = str_replace("\n", "<br>", $message[1]);

    if($type == 0) { $board = "c_nationboard.php"; }
    else { $board = "c_chiefboard.php"; }

    $imageTemp = GetImageURL($imgsvr);
    echo "
<table style='text-align:center;' width=1000 class='tb_layout bg0'>
    <tr>
        <td width=90 style='text-align:center;' class='bg1'><font size=1>$name</font></td>
        <td width=746 style='text-align:center;' class='bg1'><font size=4><b>$message[0]</b></font></td>
        <td width=148 style='text-align:center;' class='bg1'>$when</td>
    </tr>
    <tr>
        <td width=90 height=64 valign=top><img width='64' height='64' src={$imageTemp}/{$picture} border='0'></td>
        <td width=906 colspan=2>$message[1]</td>
    </tr>";
    for($i=0; $i < $count; $i++) {
        $who = Tag2Code($message[2+$i*2]);
        $reply = Tag2Code($message[3+$i*2]);
        $query = "select name from general where no='$who'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $regen = MYDB_fetch_array($result);
        echo "
    <tr>
        <td width=90 style='text-align:center;'><font size=1>{$regen['name']}</font></td>
        <td width=906 colspan=2>$reply</td>
    </tr>";
    }
    echo "
    <tr>
        <form name=reply_form{$num} method=post action=$board>
        <td width=90 style='text-align:center;'>댓글달기</td>
        <td width=906 colspan=2>
            <input type=textarea name=reply maxlength=250 style=color:white;background-color:black;width:804px;>
            <input type=submit value=댓글달기>
            <input type=hidden name=num value=$num>
        </td>
        </form>
    </tr>
</table>
<br>";
}

function banner() {

    return sprintf(
        '<font size=2>%s %s / %s <br> %s</font>',
        GameConst::$title,
        VersionGit::$version,
        GameConst::$banner,
        GameConst::$helper);
}

function addTurn($date, int $turnterm, int $turn=1) {
    $date = new \DateTime($date);
    $target = $turnterm*$turn;
    $date->add(new \DateInterval("PT{$target}M"));
    return $date->format('Y-m-d H:i:s');
}

function subTurn($date, int $turnterm, int $turn=1) {
    $date = new \DateTime($date);
    $target = $turnterm*$turn;
    $date->sub(new \DateInterval("PT{$target}M"));
    return $date->format('Y-m-d H:i:s');
}

function cutTurn($date, int $turnterm) {
    $date = new \DateTime($date);
    
    $baseDate = new \DateTime($date->format('Y-m-d'));
    $baseDate->sub(new \DateInterval("P1D"));
    $baseDate->add(new \DateInterval("PT1H"));

    $diffMin = intdiv($date->getTimeStamp() - $baseDate->getTimeStamp(), 60);
    $diffMin -= $diffMin % $turnterm;

    $baseDate->add(new \DateInterval("PT{$diffMin}M"));
    return $baseDate->format('Y-m-d H:i:s');
}

function cutDay($date, int $turnterm) {
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
    return [$baseDate->format('Y-m-d H:i:s'), $yearPulled, $newMonth];
}

function increaseRefresh($type="", $cnt=1) {
    //FIXME: 로그인, 비로그인 시 처리가 명확하지 않음
    $session = Session::getInstance();
    $userID = $session->userID;
    $generalID = $session->generalID;

    $date = date('Y-m-d H:i:s');

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->refresh = $gameStor->refresh+$cnt; //TODO: +로 증가하는 값은 별도로 분리

    if($generalID) {
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

    $date = date('Y-m-d H:i:s');
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

function tryLock() {
    //NOTE: 게임 로직과 관련한 모든 insert, update 함수들은 lock을 거칠것을 권장함.
    $db = DB::db();
    //테이블 락
    $db->query("lock tables plock write");
    // 잠금
    $db->update('plock', [
        'plock'=>1
    ], true);

    $isUnlocked = $db->affectedRows() > 0;
    
    //테이블 언락
    $db->query("unlock tables");

    return $isUnlocked;
}

function unlock() {
    // 풀림
    //NOTE: unlock에는 table lock이 필요없는가?
    $db = DB::db();
    $db->query("lock tables plock write");
    $db->update('plock', [
        'plock'=>0
    ], true);
    $db->query("unlock tables");
}

function timeover() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    list($turnterm, $turntime) = $gameStor->getValuesAsArray(['turnterm', 'turntime']);
    $diff = (new \DateTime())->getTimestamp() - (new \DateTime($turntime))->getTimestamp();

    $t = min($turnterm, 5);

    $term = $diff;
    if($term >= $t || $term < 0) { return 1; }
    else { return 0; }
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
        $threshold = 2;
    }
    else{
        $threshold = 3;
    }
    //지연 해야할 밀린 턴 횟수
    $iter = intdiv($timeMinDiff, $term);
    if($iter > $threshold) {
        $minute = $iter * $term;
        $newTurntime = $turntime->add(new \DateInterval("PT{$minute}M"));
        $newNextTurntime = $turntime->add(new \DateInterval("PT{$term}M"));
        $gameStor->turntime = $newTurntime->format('Y-m-d H:i:s');
        $gameStor->starttime = (new \DateTimeImmutable($gameStor->starttime))
            ->add(new \DateInterval("PT{$minute}M"))
            ->format('Y-m-d H:i:s');

        $db->update('general', [
            'turntime'=> $db->sqleval('DATE_ADD(turntime, INTERVAL %i MINUTE)', $minute)
        ], 'turntime<=DATE_ADD(turntime, INTERVAL %i MINUTE)', $term);
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

function checkTurn() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $alllog = [];

    // 잦은 갱신 금지 현재 5초당 1회
    if(!timeover()) { return; }
    // 현재 처리중이면 접근 불가

    // 파일락 획득
    //FIXME:이미 DB 테이블로 lock을 시도하는데 이게 따로 필요한가?
    $fp = fopen(__dir__.'/lock.txt', 'r');
    if(!flock($fp, LOCK_EX)) {
         return; 
        }

    if(!tryLock()){
        return;
    }

    $session = Session::getInstance();

    pushLockLog(["- checkTurn()      : ".date('Y-m-d H:i:s')." : ".$session->userName]);

    // 파일락 해제
    if(!flock($fp, LOCK_UN)) { return; }
    // 세마포어 해제
    //if(!@sem_release($sema)) { echo "치명적 에러! Hide_D에게 문의하세요!"; exit(1); }

    pushLockLog(["- checkTurn() 입   : ".date('Y-m-d H:i:s')." : ".$session->userName]);
    
    //if(STEP_LOG) delStepLog();
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', 진입');
    
    //천통시에는 동결
    if($gameStor->isunited == 2) {
        $db->update('plock', ['plock'=>1], true);
        return;
    }
    $gameStor->cacheAll();
    // 1턴이상 갱신 없었으면 서버 지연
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', checkDelay');
    checkDelay();
    // 접속자수, 접속국가, 국가별 접속장수 갱신
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', checkDelay');
    updateOnline();
    //접속자 수 따라서 갱신제한 변경
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', CheckOverhead');
    CheckOverhead();
    //서버정보

    $date = date('Y-m-d H:i:s');
    // 최종 처리 월턴의 다음 월턴시간 구함
    $prevTurn = cutTurn($gameStor->turntime, $gameStor->turnterm);
    $nextTurn = addTurn($prevTurn, $gameStor->turnterm);
    // 현재 턴 이전 월턴까지 모두처리.
    //최종 처리 이후 다음 월턴이 현재 시간보다 전이라면
    while($nextTurn <= $date) {
        // 월턴이전 장수 모두 처리
        $query = "select no,name,turntime,turn0,npc from general where turntime < '$nextTurn' order by turntime";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            
            //if(PROCESS_LOG) $processlog[0] = "[{$date}] 월턴 이전 갱신: name({$general['name']}), no({$general['no']}), turntime({$general['turntime']}), turn0({$general['turn0']})";
            //if(PROCESS_LOG) pushProcessLog($processlog);
            
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAI');
            if($general['npc'] >= 2) { processAI($general['no']); }    // npc AI 처리
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', PreprocessCommand');
            PreprocessCommand($general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processCommand');
            processCommand($general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateCommand');
            updateCommand($general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTurntime');
            updateTurntime($general['no']);
            
        }
        
        // 트래픽 업데이트
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTraffic');
        updateTraffic();
        // 1달마다 처리하는 것들, 벌점 감소 및 건국,전턴,합병 -1, 군량 소모
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', preUpdateMonthly');
        $result = preUpdateMonthly();
        if($result == false) {
            pushLockLog(["-- checkTurn() 오류출 : ".date('Y-m-d H:i:s')." : ".$session->userName]);

            // 잡금 해제
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', unlock');
            $gameStor->resetCache(true);
            unlock();
            return false;
        }

        // 그 시각 년도,월 저장
        list($gameStor->year, $gameStor->month) = turnDate($nextTurn);

        pushLockLog(["-- checkTurn() ".$gameStor->month."월 : ".date('Y-m-d H:i:s')." : ".$session->userName]);

        // 이벤트 핸들러 동작
        foreach (DB::db()->query('SELECT * from event') as $rawEvent) {
            $eventID = $rawEvent['id'];
            $cond = Json::decode($rawEvent['condition']);
            $action = Json::decode($rawEvent['action']);
            $event = new Event\EventHandler($cond, $action);

            $event->tryRunEvent(['currentEventID'=>$eventID] + $gameStor->getAll(true));
        }

        // 분기계산. 장수들 턴보다 먼저 있다면 먼저처리
        if($gameStor->month == 1) {
            // NPC 등장
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processGoldIncome');
            processGoldIncome();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processSpring');
            processSpring();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateYearly');
            updateYearly();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', tradeRate');
            tradeRate();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', addAge');
            addAge();
            // 새해 알림
            $alllog[] = "<C>◆</>{$gameStor->month}월:<C>{$gameStor->year}</>년이 되었습니다.";
            pushGeneralPublicRecord($alllog, $gameStor->year, $gameStor->month);
        } elseif($gameStor->month == 4) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster();
        } elseif($gameStor->month == 7) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processRiceIncome');
            processRiceIncome();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processFall');
            processFall();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', tradeRate');
            tradeRate();
        } elseif($gameStor->month == 10) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly();
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster();
        }
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', postUpdateMonthly');
        postUpdateMonthly();

        // 다음달로 넘김
        $prevTurn = $nextTurn;
        $nextTurn = addTurn($prevTurn, $gameStor->turnterm);
    }

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
        
    // 이시각 정각 시까지 업데이트 완료했음
    $gameStor->turntime = $prevTurn;

    // 그 시각 년도,월 저장
    list($gameStor->year, $gameStor->month) = turnDate($prevTurn);
    // 현재시간의 월턴시간 이후 분단위 장수 처리
    do {
        $query = "select no,name,turntime,turn0,npc from general where turntime<='$date' order by turntime";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);

            //if(PROCESS_LOG) $processlog[0] = "[{$date}] 월턴 이후 갱신: name({$general['name']}), no({$general['no']}), turntime({$general['turntime']}), turn0({$general['turn0']})";
            //if(PROCESS_LOG) pushProcessLog($processlog);
            
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAI');
            if($general['npc'] >= 2) { processAI($general['no']); }    // npc AI 처리
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', PreprocessCommand');
            PreprocessCommand($general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processCommand');
            processCommand($general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateCommand');
            updateCommand($general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTurntime');
            updateTurntime($general['no']);
        }
    } while($gencount > 0);

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
    
    $gameStor->turntime = $date;

    // 부상 과도 제한
    $query = "update general set injury='80' where injury>'80'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //토너먼트 처리
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processTournament');
    processTournament();
    //거래 처리
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAuction');
    processAuction();
    // 잡금 해제
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', unlock');
    $gameStor->resetCache(true);
    unlock();

    pushLockLog(["- checkTurn()   출 : ".date('Y-m-d H:i:s')." : ".$session->userName]);

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', finish');
    
    return true;
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
        $query = "select no,name,nation,leader,power,intel from general where specage<=age and special='0'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            $special = getSpecial($general['leader'], $general['power'], $general['intel']);
            $query = "update general set special='$special' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $josaUl = JosaUtil::pick($special, '을');
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special)."</></b>】{$josaUl} 습득");
            pushGenLog($general, "<C>●</>특기 【<b><L>".getGenSpecial($special)."</></b>】{$josaUl} 익혔습니다!");
        }

        $query = "select no,name,nation,leader,power,intel,npc,dex0,dex10,dex20,dex30,dex40 from general where specage2<=age and special2='0'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            $special2 = getSpecial2($general['leader'], $general['power'], $general['intel'], 0, $general['dex0'], $general['dex10'], $general['dex20'], $general['dex30'], $general['dex40']);

            $query = "update general set special2='$special2' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $josaUl = JosaUtil::pick($special2, '을');
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special2)."</></b>】{$josaUl} 습득");
            pushGenLog($general, "<C>●</>특기 【<b><L>".getGenSpecial($special2)."</></b>】{$josaUl} 익혔습니다!");
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

function PreprocessCommand($no) {
    $db = DB::db();
    $connect=$db->get();
    $log = [];

    $query = "select no,name,city,injury,special2,item,turn0 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($general['special2'] == 73 || $general['item'] == 23 || $general['item'] == 24) {
        //특기보정 : 의술
        //의서 사용
        if($general['injury'] > 0) {
            $general['injury'] = 0;
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($general, "<C>●</><C>의술</>을 펼쳐 스스로 치료합니다!");
        }
            
        $query = "select no,name,injury from general where city='{$general['city']}' and injury>10 order by rand()";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $patientCount = MYDB_num_rows($result);
    
        if($patientCount > 0) {
            // 50% 확률로 치료
            $patientCount = Util::round($patientCount * 0.5);
    
            $patientName = "";
            for($i=0; $i < $patientCount; $i++) {
                $patient = MYDB_fetch_array($result);
    
                //부상 치료
                $query = "update general set injury=0 where no='{$patient['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    
                $josaYi = JosaUtil::pick($general['name'], '이');
                pushGenLog($patient, "<C>●</><Y>{$general['name']}</>{$josaYi} <C>의술</>로써 치료해줍니다!");
                
                if($patientName == "") {
                    $patientName = $patient['name'];
                }
            }

            if($patientCount == 1) {
                $josaUl = JosaUtil::pick($patientName, '을');
                pushGenLog($general, "<C>●</><C>의술</>을 펼쳐 도시의 장수 <Y>{$patientName}</>{$josaUl} 치료합니다!");
            } else {
                $patientCount -= 1;
                pushGenLog($general, "<C>●</><C>의술</>을 펼쳐 도시의 장수들 <Y>{$patientName}</> 외 <C>{$patientCount}</>명을 치료합니다!");
            }
        }
    }
    
    if($general['injury'] > 0) {
        if($general['item'] >=7 && $general['item'] <= 11) {
            //영구약 사용
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $josaUl = JosaUtil::pick($general['item'], '을');
            $log[0] = "<C>●</><C>".getItemName($general['item'])."</>{$josaUl} 사용하여 치료합니다!";
            pushGenLog($general, $log);
        } elseif($general['injury'] > 10 && $general['item'] == 1 && $general['turn0'] != EncodeCommand(0, 0, 0, 50)) {
            //환약 사용
            $query = "update general set injury=0,item=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</><C>환약</>을 사용하여 치료합니다!";
            pushGenLog($general, $log);
        } elseif($general['injury'] > 10) {
            //부상 감소
            $query = "update general set injury=injury-10 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            //부상 감소
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
}


function updateTurntime($no) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $alllog = [];
    $log = [];

    $admin = $gameStor->getValues(['year', 'month', 'isunited', 'turnterm']);

    $query = "select no,name,name2,nation,troop,age,turntime,killturn,level,deadyear,npc,npc_org,affinity,npcid from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    // 삭턴장수 삭제처리
    if($general['killturn'] <= 0) {
        // npc유저 삭턴시 npc로 전환
        if($general['npc'] == 1 && $general['deadyear'] > $admin['year']) {
            $general['killturn'] = ($general['deadyear'] - $admin['year']) * 12;
            $general['npc'] = $general['npc_org'];
            $query = "update general set owner=-1,npc='{$general['npc']}',killturn='{$general['killturn']}',mode=2 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $josaYi = JosaUtil::pick($general['name2'], '이');
            $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name2']}</>{$josaYi} <Y>{$general['name']}</>의 육체에서 <S>유체이탈</>합니다!";
            pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
        } else {
            // 군주였으면 유지 이음
            if($general['level'] == 12) {
                nextRuler($general);
            }

            //도시의 태수, 군사, 시중직도 초기화
            $query = "update city set gen1='0' where gen1='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update city set gen2='0' where gen2='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update city set gen3='0' where gen3='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 부대 처리
            $query = "select no from troop where troop='{$general['troop']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $troop = MYDB_fetch_array($result);

            //부대장일 경우
            if($troop['no'] == $general['no']) {
                // 모두 탈퇴
                $query = "update general set troop='0' where troop='{$general['troop']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                // 부대 삭제
                $query = "delete from troop where troop='{$general['troop']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $query = "update general set troop='0' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
            // 장수 삭제
            $query = "delete from general where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //기존 국가 기술력 그대로
            $query = "select no from general where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
            $gennum = $gencount;
            if($gencount < GameConst::$initialNationGenLimit) $gencount = GameConst::$initialNationGenLimit;

            $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 병, 요절, 객사, 번개, 사채, 일확천금, 호랑이, 곰, 수영, 처형, 발견
            $josaYi = JosaUtil::pick($general['name'], '이');
            switch(rand()%42) {
            case 0:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 역병에 걸려 <R>죽고</> 말았습니다."; break;
            case 1:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <R>요절</>하고 말았습니다."; break;
            case 2:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 거리에서 갑자기 <R>객사</>하고 말았습니다."; break;
            case 3:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 안타깝게도 번개에 맞아 <R>죽고</> 말았습니다."; break;
            case 4:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 고리대금에 시달리다가 <R>자살</>하고 말았습니다."; break;
            case 5:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 일확천금에 놀라 심장마비로 <R>죽고</> 말았습니다."; break;
            case 6:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 산속에서 호랑이에게 물려 <R>죽고</> 말았습니다."; break;
            case 7:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 산책중 곰에게 할퀴어 <R>죽고</> 말았습니다."; break;
            case 8:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 수영을 하다 <R>익사</>하고 말았습니다."; break;
            case 9:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 황제를 모독하다가 <R>처형</>당하고 말았습니다."; break;
            case 10: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 이튿날 침실에서 <R>죽은채로</>발견되었습니다."; break;
            case 11: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 색에 빠져 기력이 쇠진해 <R>죽고</>말았습니다."; break;
            case 12: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 미녀를 보고 심장마비로 <R>죽고</>말았습니다."; break;
            case 13: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 우울증에 걸려 <R>자살</>하고 말았습니다."; break;
            case 14: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 천하 정세를 비관하며 <R>분신</>하고 말았습니다."; break;
            case 15: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 어떤 관심도 못받고 쓸쓸히 <R>죽고</>말았습니다."; break;
            case 16: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 유산 상속 문제로 다투다가 <R>살해</>당했습니다."; break;
            case 17: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 누군가의 사주로 자객에게 <R>암살</>당했습니다."; break;
            case 18: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 바람난 배우자에게 <R>독살</>당하고 말았습니다."; break;
            case 19: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 농약을 술인줄 알고 마셔 <R>죽고</>말았습니다."; break;
            case 20: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 아무 이유 없이 <R>죽고</>말았습니다."; break;
            case 21: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 전재산을 잃고 화병으로 <R>죽고</>말았습니다."; break;
            case 22: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 단식운동을 하다가 굶어 <R>죽고</>말았습니다."; break;
            case 23: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 귀신에게 홀려 시름 앓다가 <R>죽고</>말았습니다."; break;
            case 24: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 사람들에게 집단으로 맞아서 <R>죽고</>말았습니다."; break;
            case 25: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 갑자기 성벽에서 뛰어내려 <R>죽고</>말았습니다."; break;
            case 26: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 농사중 호미에 머리를 맞아 <R>죽고</>말았습니다."; break;
            case 27: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 저세상이 궁금하다며 <R>자살</>하고 말았습니다."; break;
            case 28: $alllog[0] = "<C>●</>{$admin['month']}월:운좋기로 소문난 <Y>{$general['name']}</>{$josaYi} 불운하게도 <R>죽고</>말았습니다."; break;
            case 29: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 무리하게 단련을 하다가 <R>죽고</>말았습니다."; break;
            case 30: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 생활고를 비관하며 <R>자살</>하고 말았습니다."; break;
            case 31: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 평생 결혼도 못해보고 <R>죽고</> 말았습니다."; break;
            case 32: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 과식하다 배가 터져 <R>죽고</> 말았습니다."; break;
            case 33: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 웃다가 숨이 넘어가 <R>죽고</> 말았습니다."; break;
            case 34: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 추녀를 보고 놀라서 <R>죽고</> 말았습니다."; break;
            case 35: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 물에 빠진 사람을 구하려다 같이 <R>죽고</> 말았습니다."; break;
            case 36: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 독살을 준비하다 독에 걸려 <R>죽고</> 말았습니다."; break;
            case 37: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 뒷간에서 너무 힘을 주다가 <R>죽고</> 말았습니다."; break;
            case 38: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 돌팔이 의사에게 치료받다가 <R>죽고</> 말았습니다."; break;
            case 39: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 남의 보약을 훔쳐먹다 부작용으로 <R>죽고</> 말았습니다."; break;
            case 40: $alllog[0] = "<C>●</>{$admin['month']}월:희대의 사기꾼 <Y>{$general['name']}</>{$josaYi} <R>사망</>했습니다."; break;
            case 41: $alllog[0] = "<C>●</>{$admin['month']}월:희대의 호색한 <Y>{$general['name']}</>{$josaYi} <R>사망</>했습니다."; break;
            default: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <R>사망</>했습니다."; break;
            }
            // 엔피씨,엠피씨,의병 사망로그
            if($general['npc'] == 2) {
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <R>사망</>했습니다.";
            } elseif($general['npc'] >= 3) {
                switch(rand()%10) {
                case 0: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 푸대접에 실망하여 떠났습니다."; break;
                case 1: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 갑자기 화를 내며 떠났습니다."; break;
                case 2: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 의견차이를 좁히지 못하고 떠났습니다."; break;
                case 3: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 판단 착오였다며 떠났습니다."; break;
                case 4: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 생활고가 나아지지 않는다며 떠났습니다."; break;
                case 5: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 기대가 너무 컸다며 떠났습니다."; break;
                case 6: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 아무 이유 없이 떠났습니다."; break;
                case 7: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 자기 목적은 달성했다며 떠났습니다."; break;
                case 8: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 자기가 없어도 될것 같다며 떠났습니다."; break;
                case 9: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 처자식이 그립다며 떠났습니다."; break;
                }
            }

            pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);

            return;
        }
    }

    if($general['age'] >= 80 && $general['npc'] == 0) {
        if($admin['isunited'] == 0) {
            CheckHall($no);
        }

        $query = "update general set leader=leader*0.85,power=power*0.85,intel=intel*0.85,injury=0,experience=experience*0.5,dedication=dedication*0.5,firenum=0,warnum=0,killnum=0,deathnum=0,killcrew=0,deathcrew=0,age=20,specage=0,specage2=0,crew=crew*0.85,dex0=dex0*0.5,dex10=dex10*0.5,dex20=dex20*0.5,dex30=dex30*0.5,dex40=dex40*0.5 where no='$no'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $josaYi = JosaUtil::pick($general['name'], '이');
        $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <R>은퇴</>하고 그 자손이 유지를 이어받았습니다.";
        pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);

        $log[0] = "<C>●</>나이가 들어 <R>은퇴</>하고 자손에게 자리를 물려줍니다.";
        pushGenLog($general, $log);
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:나이가 들어 은퇴하고, 자손에게 관직을 물려줌");
    }

    $turntime = addTurn($general['turntime'], $admin['turnterm']);

    $query = "update general set turntime='$turntime' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
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

    $unitedDate = date('Y-m-d H:i:s');
    $nation = getNationStaticInfo($general['nation']);

    $serverCnt = $db->queryFirstField('SELECT count(*) FROM ng_games');

    [$scenarioIdx, $scenarioName, $startTime] = $gameStor->getValuesAsArray(['scenario', 'scenario_text', 'starttime']);

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
            'owner_name'=>$general['name2'],
            'serverID'=>UniqueConst::$serverID,
            'serverIdx'=>$serverCnt,
            'serverName'=>UniqueConst::$serverName,
            'scenarioName'=>$scenarioName,
        ];
        $jsonAux = Json::encode($aux);

        $db->insertUpdate('ng_hall', [
            'server_id'=>UniqueConst::$serverID,
            'scenario'=>$scenarioIdx,
            'general_no'=>$no,
            'type'=>$idx,
            'value'=>$general[$typeName]??0,
            'owner'=>$general['owner']??null,
            'aux'=>$jsonAux
        ],[
            'value'=>$general[$typeName]??0,
            'aux'=>$jsonAux
        ]);
    }
}


function uniqueItem($general, $log, $vote=0) {
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

    if($admin['scenario'] == 0)  { $prob = $gen['cnt'] * 5; }  // 5~6개월에 하나씩 등장
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

        $log[] = "<C>●</><Y>통솔</>이 <C>1</> 올랐습니다!";
    }

    if($general['power2'] < 0) {
        $query = "update general set power2='$limit'+power2,power=power-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><R>무력</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['power2'] >= $limit) {
        $query = "update general set power2=power2-'$limit',power=power+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><Y>무력</>이 <C>1</> 올랐습니다!";
    }

    if($general['intel2'] < 0) {
        $query = "update general set intel2='$limit'+intel2,intel=intel-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><R>지력</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['intel2'] >= $limit) {
        $query = "update general set intel2=intel2-'$limit',intel=intel+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[] = "<C>●</><Y>지력</>이 <C>1</> 올랐습니다!";
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

function deleteNation($general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    $josaUn = JosaUtil::pick($nation['name'], '은');
    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【멸망】</b></><D><b>{$nation['name']}</b></>{$josaUn} <R>멸망</>했습니다.";

    // 전 장수 재야로    // 전 장수 소속 무소속으로
    $query = "update general set belong=0,troop=0,level=0,nation=0,makelimit=12 where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 도시 공백지로
    $query = "update city set nation=0,front=0,gen1=0,gen2=0,gen3=0 where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 부대 삭제
    $query = "delete from troop where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 국가 삭제
    $query = "delete from nation where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 외교 삭제
    $query = "delete from diplomacy where me='{$general['nation']}' or you='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushWorldHistory($history, $admin['year'], $admin['month']);
    refreshNationStaticInfo();
}

function nextRuler($general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select nation,name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no,name from general where nation='{$general['nation']}' and level!='12' and level>='9' order by level desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $corecount = MYDB_num_rows($result);

    //npc or npc유저인 경우 후계 찾기
    if($general['npc'] > 0) {
        $query = "select no,name,nation,IF(ABS(affinity-'{$general['affinity']}')>75,150-ABS(affinity-'{$general['affinity']}'),ABS(affinity-'{$general['affinity']}')) as npcmatch2 from general where nation='{$general['nation']}' and level!=12 and npc>0 order by npcmatch2,rand() limit 0,1";
        $npcresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($npcresult);
    } else {
        $npccount = 0;
    }

    // 수뇌부가 없으면 공헌도 최고 장수
    if($npccount > 0) {
        $nextruler = MYDB_fetch_array($npcresult);
        //국명 교체
        //$query = "update nation set name='{$nextruler['name']}' where nation='{$general['nation']}'";
        //MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($corecount == 0) {
        $query = "select no,name from general where nation='{$general['nation']}' and level!='12' order by dedication desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $corecount = MYDB_num_rows($result);

        // 아무도 없으면 국가 삭제
        if($corecount == 0) {
            //분쟁기록 모두 지움
            DeleteConflict($general['nation']);
            deleteNation($general);
            return;
        } else {
            $nextruler = MYDB_fetch_array($result);
        }
    } else {
        $nextruler = MYDB_fetch_array($result);
    }

    //군주 교체
    $query = "update general set level='12' where no='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //도시관직해제
    $query = "update city set gen1=0 where gen1='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //도시관직해제
    $query = "update city set gen2=0 where gen2='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //도시관직해제
    $query = "update city set gen3=0 where gen3='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $josaYi = JosaUtil::pick($nextruler['name'], '이');
    $history = ["<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【유지】</b></><Y>{$nextruler['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>의 유지를 이어 받았습니다"];

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【유지】</b></><Y>{$nextruler['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>의 유지를 이어 받음.");
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

function CharDedication($ded, $personal) {
    switch($personal) {
        case 10:
            $ded *= 0.9; break;
    }
    $ded = Util::round($ded);

    return $ded;
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

function SabotageInjury($city, $type=0) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();
    $log = [];

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
        }
    }
}

function getRandTurn($term) {
    $randtime = rand(0, 60 * $term - 1);
    $turntime = date('Y-m-d H:i:s', strtotime('now') + $randtime);

    return $turntime;
}

function getRandTurn2($term) {
    $randtime = rand(0, 60 * $term - 1);
    $turntime = date('Y-m-d H:i:s', strtotime('now') - $randtime);

    return $turntime;
}
