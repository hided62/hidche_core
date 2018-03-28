<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');
require_once 'process_war.php';
require_once 'func_gamerule.php';
require_once 'func_process.php';
require_once 'func_process_trick.php';
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
require_once('func_diplomacy.php');
require_once('func_command.php');





/** 
 * 로그인한 유저의 장수 id를 받아옴
 * 
 * @return int|null
 */
function getGeneralID($forceExit=false, $countLogin=true){
    $userID = Session::getUserID();
    if(!$userID){ //유저id 없으면 어차피 의미 없음.
        return null;
    }

    $idKey = DB::prefix().'p_no';
    $generalID = Util::array_get($_SESSION[$idKey], null);

    if($generalID){
        return $generalID;
    }

    $db = DB::db();
    //흠?
    $generalID = $db->queryFirstField('select no from general where owner = %i', $userID);
    if(!$generalID && $forceExit){
        header('Location:..');
        die();
    }

    if($generalID && $countLogin){
        //로그인으로 처리
        //XXX: 'get' 함수인데 update가 들어가있다.
        //TODO: 조금더 적절한 형태의 로그인 카운트를 생각해볼 것
        $query=$db->query("update general set logcnt=logcnt+1 ,ip = %s_ip,lastconnect=%s_lastConnect,conmsg=%s_conmsg where owner= %s_userID",[
            'ip' => getenv("REMOTE_ADDR"),
            'lastConnect' => date('Y-m-d H:i:s'),
            'conmsg' => Util::array_get($_SESSION['conmsg'], ''),
            'userID' => $userID
        ]);
        $_SESSION[$idKey] = $generalID;
    }
    
    return $generalID;
}

/** 
 * 로그인한 유저의 장수명을 받아옴
 * 
 * @return string|null
 */
function getGeneralName($forceExit=false)
{
    $generalID = getGeneralID();
    if(!$generalID){
        if($forceExit){
            header('Location:..');
            die();
        }

        return null;
    }

    $nameKey = DB::prefix().'p_name';
    $generalName = Util::array_get($_SESSION[$nameKey], null);

    if($generalName){
        return $generalName;
    }

    //흠?
    $generalName = DB::db()->queryFirstField('select name from general where no = %i', $generalID);
    if(!$generalName){
        //이게 말이 돼?
        resetSessionGeneralValues();
        if($forceExit){
            header('Location:..');
            die();
        }
    }

    if($generalName){
        $_SESSION[$nameKey] = $generalName;
    }

    return $generalName;
}

/**
 * nationID를 이용하여 국가의 '어지간해선' 변경되지 않는 정보(이름, 색, 성향, 규모, 수도)를 반환해줌
 * 
 * @param int|null $nationID 국가 코드, -1인 경우 전체, null인 경우 수행하지 않음
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

    if($nationID === null || $nationID == 0){
        return null;
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

function GetImageURL($imgsvr) {
    global $image, $image1;
    if($imgsvr == 0) {
        return $image;
    } else {
        return $image1;
    }
}

/**
 * generalID를 이용해 각 서버 등록 여부를 확인함
 * 
 * FIXME: 현재의 구현으로는 session에 p_no가 남아있지만 general 테이블에서는 삭제되어있는 진풍경(!)이 펼쳐질 수도 있음.
 * 장수가 사망했을 때에 바로 테이블에서 삭제하는 것이 아니라 삭제 플래그를 설정하는 것이 나을 것임.
 * (퀘 섭등 NPC가 빙의할 경우에도 마찬가지!)
 * 
 * 세팅된 플래그는 새롭게 장수를 생성하거나, 새로 빙의할 때 초기화하는 것이 적절한 해결책일 것.
 * 
 * @return bool
 */
function isSigned(){
    $generalID = getGeneralID();
    if(!$generalID){
        return false;
    }
    return true;
}


function checkLimit($con, $conlimit) {
    if(Session::getUserGrade()>=4){
        return 0;
    }
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
    return DB::db()->queryFirstField('select block from general where no = %i', getGeneralID());
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



function cityInfo($connect) {
    global $_basecolor, $_basecolor2, $images;

    $query = "select no,city,skin from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    // 도시 정보
    $city = getCity($connect, $me['city']);

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

    if($nation['color'] == "" || $me['skin'] < 1) { $nation['color'] = "000000"; }
    echo "<table width=640 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr><td colspan=8 align=center style=height:20;color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13px;>【 ".getRegion($city['region'])." | ".getCityLevel($city['level'])." 】 {$city['name']}</td></tr>
    <tr><td colspan=8 align=center style=height:20;color:".newColor($nation['color']).";background-color:{$nation['color']}><b>";

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
        $gen1['name'] = '-';
    }

    if($city['gen2'] > 0) {
        $query = "select name from general where no='{$city['gen2']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen2 = MYDB_fetch_array($result);
    } else {
        $gen2['name'] = '-';
    }

    if($city['gen3'] > 0) {
        $query = "select name from general where no='{$city['gen3']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen3 = MYDB_fetch_array($result);
    } else {
        $gen3['name'] = '-';
    }

    echo "
        </b></td>
    </tr>
    <tr>
        <td rowspan=2 align=center id=bg1><b>주민</b></td>
        <td height=7 colspan=3>".bar($pop, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>민심</b></td>
        <td height=7>".bar($rate, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>태수</b></td>
        <td rowspan=2 align=center>{$gen1['name']}</td>
    </tr>
    <tr>
        <td colspan=3 align=center>{$city['pop']}/{$city['pop2']}</td>
        <td align=center>{$city['rate']}</td>
    </tr>
    <tr>
        <td width=50  rowspan=2 align=center id=bg1><b>농업</b></td>
        <td width=100 height=7>".bar($agri, $me['skin'])."</td>
        <td width=50  rowspan=2 align=center id=bg1><b>상업</b></td>
        <td width=100 height=7>".bar($comm, $me['skin'])."</td>
        <td width=50  rowspan=2 align=center id=bg1><b>치안</b></td>
        <td width=100 height=7>".bar($secu, $me['skin'])."</td>
        <td width=50  rowspan=2 align=center id=bg1><b>군사</b></td>
        <td rowspan=2 align=center>{$gen2['name']}</td>
    </tr>
    <tr>
        <td align=center>{$city['agri']}/{$city['agri2']}</td>
        <td align=center>{$city['comm']}/{$city['comm2']}</td>
        <td align=center>{$city['secu']}/{$city['secu2']}</td>
    </tr>
    <tr>
        <td rowspan=2 align=center id=bg1><b>수비</b></td>
        <td height=7>".bar($def, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>성벽</b></td>
        <td height=7>".bar($wall, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>시세</b></td>
        <td height=7>".bar($trade, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>시중</b></td>
        <td rowspan=2 align=center>{$gen3['name']}</td>
    </tr>
    <tr>
        <td align=center>{$city['def']}/{$city['def2']}</td>
        <td align=center>{$city['wall']}/{$city['wall2']}</td>
        <td align=center>{$tradeStr}</td>
    </tr>
</table>
";
}

function myNationInfo($connect) {
    global $_basecolor, $_basecolor2, $images;

    $query = "select startyear,year from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select skin,no,nation from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select nation,name,color,power,msg,gold,rice,bill,rate,scout,war,tricklimit,surlimit,tech,totaltech,level,type from nation where nation='{$me['nation']}'";
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

    echo "<table width=498 height=190 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr>
        <td colspan=4 align=center ";

    if($me['skin'] < 1) {
        if($me['nation'] == 0) { echo "style=font-weight:bold;font-size:13px;>【재 야】"; }
        else { echo "style=font-weight:bold;font-size:13px;>국가【 {$nation['name']} 】"; }
    } else {
        if($me['nation'] == 0) { echo "style=color:white;background-color:000000;font-weight:bold;font-size:13px;>【재 야】"; }
        else { echo "style=color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13px;>국가【 {$nation['name']} 】"; }
    }

    echo "
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><b>성 향</b></td>
        <td align=center colspan=3><font color=\"yellow\">".getNationType($nation['type'])."</font> (".getNationType2($nation['type']).")</td>
        </td>
    </tr>
    <tr>
        <td width=68  align=center id=bg1><b>".getLevel(12, $nation['level'])."</b></td>
        <td width=178 align=center>";echo $level12==''?"-":"{$level12['name']}"; echo "</td>
        <td width=68  align=center id=bg1><b>".getLevel(11, $nation['level'])."</b></td>
        <td width=178 align=center>";echo $level11==''?"-":"{$level11['name']}"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>총주민</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$city['totpop']}/{$city['maxpop']}";echo "</td>
        <td align=center id=bg1><b>총병사</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$general['totcrew']}/{$general['maxcrew']}"; echo "</td>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><b>국 고</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$nation['gold']}";echo "</td>
        <td align=center id=bg1><b>병 량</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$nation['rice']}";echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>지급율</b></td>
        <td align=center>";
    if($me['nation'] == 0) {
        echo "해당 없음";
    } else {
        echo $nation['bill']==0?"0 %":"{$nation['bill']} %";
    }
    echo "
        </td>
        <td align=center id=bg1><b>세 율</b></td>
        <td align=center>";
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
        $nation['tricklimit'] = "<font color=white>해당 없음</font>";
        $nation['surlimit'] = "<font color=white>해당 없음</font>";
        $nation['scout'] = "<font color=white>해당 없음</font>";
        $nation['war'] = "<font color=white>해당 없음</font>";
        $nation['power'] = "<font color=white>해당 없음</font>";
    } else {
        if($nation['tricklimit'] != 0) { $nation['tricklimit'] = "<font color=red>{$nation['tricklimit']}턴</font>"; }
        else { $nation['tricklimit'] = "<font color=limegreen>가 능</font>"; }
    
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
        <td align=center id=bg1><b>속 령</b></td>
        <td align=center>";echo $me['nation']==0?"-":"{$city['cnt']}"; echo "</td>
        <td align=center id=bg1><b>장 수</b></td>
        <td align=center>";echo $me['nation']==0?"-":"{$general['cnt']}"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>국 력</b></td>
        <td align=center>{$nation['power']}</td>
        <td align=center id=bg1><b>기술력</b></td>
        <td align=center>";echo $me['nation']==0?"-":"{$nation['tech']}"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>전 략</b></td>
        <td align=center>{$nation['tricklimit']}</td>
        <td align=center id=bg1><b>외 교</b></td>
        <td align=center>{$nation['surlimit']}</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>임 관</b></td>
        <td align=center>{$nation['scout']}</td>
        <td align=center id=bg1><b>전 쟁</b></td>
        <td align=center>{$nation['war']}</td>
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

function commandTable($connect) {
    $query = "select startyear,year,develcost,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,npc,troop,city,nation,level,crew,makelimit,special from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $troop = getTroop($connect, $me['troop']);
    $city = getCity($connect, $me['city']);

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

    $develcostA = round($develcostA);
    $develcostB = round($develcostB);
    $develcostC = round($develcostC);
    $develcostD = round($develcostD);
    $develcostE = round($develcostE);

    echo "
<select name=commandtype size=1 style=width:260px;color:white;background-color:black;font-size:12;>";
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
    //TODO:등용장 재 디자인
    //xxx:등용장 일단 끔
    /*
    if($me['level'] >= 1 && $city['supply'] != 0) {
        addCommand("등용(자금{$develcost5}+장수가치)", 22);
    } else {
        addCommand("등용(자금{$develcost5}+장수가치)", 22, 0);
    }
    */
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
    //    addCommand("기습(통, 무, 지, 자금$develcost10, 군량$develcost10)", 36);
    } else {
        addCommand("화계(지력경험, 자금$develcost5, 군량$develcost5)", 32, 0);
        addCommand("탈취(무력경험, 자금$develcost5, 군량$develcost5)", 33, 0);
        addCommand("파괴(무력경험, 자금$develcost5, 군량$develcost5)", 34, 0);
        addCommand("선동(통솔경험, 자금$develcost5, 군량$develcost5)", 35, 0);
    //    addCommand("기습(통, 무, 지, 자금500, 군량500)", 36, 0);
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
            addCommand("방랑", 47);
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

function CoreCommandTable($connect) {
    $query = "select develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,nation,city,level from general where owner='{$_SESSION['userID']}'";
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
    if($citycount <= 4) {
        addCommand("통합 제의", 53, $valid);
    } else {
        addCommand("통합 제의", 53, 0);
    }

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
    if($citycount >= 5) {
        addCommand("초토화", 65, $valid);
    } else {
        addCommand("초토화", 65, 0);
    }
    addCommand("천도/3턴(금쌀{$admin['develcost']}0)", 66, $valid);
    $cost = $admin['develcost'] * 500 + 60000;   // 7만~13만
    addCommand("증축/6턴(금쌀{$cost})", 67, $valid);
    addCommand("감축/6턴", 68, $valid);
    commandGroup("", 1);
    commandGroup("====== 전 략 ======");
    $term = round(sqrt($genCount*8)*10);
    addCommand("필사즉생/3턴(전략{$term})", 71, $valid);
    $term = round(sqrt($genCount*4)*10);
    addCommand("백성동원/1턴(전략{$term})", 72, $valid);
    $term = round(sqrt($genCount*4)*10);
    addCommand("수몰/3턴(전략{$term})", 73, $valid);
    $term = round(sqrt($genCount*4)*10);
    addCommand("허보/2턴(전략{$term})", 74, $valid);
    $term = round(sqrt($genCount*2)*10);
    if($term < 72) { $term = 72; }
    addCommand("피장파장/3턴(전략{$term})", 75, $valid);
    $term = round(sqrt($genCount*10)*10);
    addCommand("의병모집/3턴(전략{$term})", 76, $valid);
    $term = round(sqrt($genCount*16)*10);
    addCommand("이호경식/1턴(전략{$term})", 77, $valid);
    $term = round(sqrt($genCount*16)*10);
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

function myInfo($connect) {
    $query = "select no,skin from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    generalInfo($connect, $me['no'], $me['skin']);
}

function generalInfo($connect, $no, $skin) {
    global $_basecolor, $_basecolor2, $image, $images;

    $query = "select img from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select skin from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

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

    $troop = getTroop($connect, $general['troop']);

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
    $typename = getTypename($general['crewtype']);
    $weapname = getWeapName($general['weap']);
    $bookname = getBookName($general['book']);
    $horsename = getHorseName($general['horse']);
    $itemname = getItemName($general['item']);
    if($general['injury'] > 0) {
        $leader = floor($general['leader'] * (100 - $general['injury'])/100);
        $power = floor($general['power'] * (100 - $general['injury'])/100);
        $intel = floor($general['intel'] * (100 - $general['injury'])/100);
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

    if($nation['color'] == "" || $skin < 1) { $nation['color'] = "000000"; }

    if($general['age'] < 60)     { $general['age'] = "<font color=limegreen>{$general['age']} 세</font>"; }
    elseif($general['age'] < 80) { $general['age'] = "<font color=yellow>{$general['age']} 세</font>"; }
    else                  { $general['age'] = "<font color=red>{$general['age']} 세</font>"; }

    $general['connect'] = round($general['connect'] / 10, 0) * 10;
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

    $weapImage = "{$images}/weap{$general['crewtype']}.jpg";
    if($admin['show_img_level'] < 2) { $weapImage = "{$image}/default.jpg"; };
    $imageTemp = GetImageURL($general['imgsvr']);
    echo "<table width=498 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr>
        <td width=64 height=64 align=center rowspan=3"; echo $skin>0?" background={$imageTemp}/{$general['picture']}":""; echo ">&nbsp;</td>
        <td align=center colspan=9 height=16 style=color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13px;>{$specUser} {$general['name']} 【 {$level} | {$call} | {$color}{$injury}</font> 】 ".substr($general['turntime'], 11)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>통솔</b></td>
        <td align=center>&nbsp;{$color}{$leader}</font>{$lbonus}&nbsp;</td>
        <td align=center width=45>".bar(expStatus($general['leader2']), $skin, 20)."</td>
        <td align=center id=bg1><b>무력</b></td>
        <td align=center>&nbsp;{$color}{$power}</font>&nbsp;</td>
        <td align=center width=45>".bar(expStatus($general['power2']), $skin, 20)."</td>
        <td align=center id=bg1><b>지력</b></td>
        <td align=center>&nbsp;{$color}{$intel}</font>&nbsp;</td>
        <td align=center width=45>".bar(expStatus($general['intel2']), $skin, 20)."</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>명마</b></td>
        <td align=center colspan=2><font size=1>$horsename</font></td>
        <td align=center id=bg1><b>무기</b></td>
        <td align=center colspan=2><font size=1>$weapname</font></td>
        <td align=center id=bg1><b>서적</b></td>
        <td align=center colspan=2><font size=1>$bookname</font></td>
    </tr>
    <tr>
        <td align=center height=64 rowspan=3"; echo $skin>0?" background={$weapImage}":""; echo ">&nbsp;</td>
        <td align=center id=bg1><b>자금</b></td>
        <td align=center colspan=2>{$general['gold']}</td>
        <td align=center id=bg1><b>군량</b></td>
        <td align=center colspan=2>{$general['rice']}</td>
        <td align=center id=bg1><b>도구</b></td>
        <td align=center colspan=2><font size=1>$itemname</font></td>
    </tr>
    <tr>
        <td align=center id=bg1><b>병종</b></td>
        <td align=center colspan=2>$typename</td>
        <td align=center id=bg1><b>병사</b></td>
        <td align=center colspan=2>{$general['crew']}</td>
        <td align=center id=bg1><b>성격</b></td>
        <td align=center colspan=2>".getGenChar($general['personal'])."</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>훈련</b></td>
        <td align=center colspan=2>$train</td>
        <td align=center id=bg1><b>사기</b></td>
        <td align=center colspan=2>$atmos</td>
        <td align=center id=bg1><b>특기</b></td>
        <td align=center colspan=2>$special / $special2</td>
    </tr>
    <tr height=20>
        <td align=center id=bg1><b>Lv</b></td>
        <td align=center>&nbsp;{$general['explevel']}&nbsp;</td>
        <td align=center colspan=5>".bar(getLevelPer($general['experience'], $general['explevel']), $skin, 20)."</td>
        <td align=center id=bg1><b>연령</b></td>
        <td align=center colspan=2>{$general['age']}</td>
    </tr>
    <tr height=20>
        <td align=center id=bg1><b>수비</b></td>
        <td align=center colspan=3>{$general['mode']}</td>
        <td align=center id=bg1><b>삭턴</b></td>
        <td align=center colspan=2>{$general['killturn']} 턴</td>
        <td align=center id=bg1><b>실행</b></td>
        <td align=center colspan=2>$remaining 분 남음</td>
    </tr>
    <tr height=20>
        <td align=center id=bg1><b>부대</b></td>
        <td align=center colspan=3>{$troop['name']}</td>
        <td align=center id=bg1><b>벌점</b></td>
        <td align=center colspan=5>".getConnect($general['connect'])." {$general['connect']}({$general['con']})</td>
    </tr>
</table>";
}

function myInfo2($connect) {
    $query = "select no,skin from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    generalInfo2($connect, $me['no'], $me['skin']);
}

function generalInfo2($connect, $no, $skin) {
    global $_basecolor, $_basecolor2, $image, $images, $_dexLimit;

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

    $dex0  = $general['dex0']  / $_dexLimit * 100;
    $dex10 = $general['dex10'] / $_dexLimit * 100;
    $dex20 = $general['dex20'] / $_dexLimit * 100;
    $dex30 = $general['dex30'] / $_dexLimit * 100;
    $dex40 = $general['dex40'] / $_dexLimit * 100;

    if($dex0 > 100) { $dex0 = 100; }
    if($dex10 > 100) { $dex10 = 100; }
    if($dex20 > 100) { $dex20 = 100; }
    if($dex30 > 100) { $dex30 = 100; }
    if($dex40 > 100) { $dex40 = 100; }

    $general['dex0']  = getDexCall($general['dex0']);
    $general['dex10'] = getDexCall($general['dex10']);
    $general['dex20'] = getDexCall($general['dex20']);
    $general['dex30'] = getDexCall($general['dex30']);
    $general['dex40'] = getDexCall($general['dex40']);

    echo "<table width=498 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr><td align=center colspan=6 id=bg1><b>추 가 정 보</b></td></tr>
    <tr>
        <td align=center id=bg1><b>명성</b></td>
        <td align=center>$experience</td>
        <td align=center id=bg1><b>계급</b></td>
        <td align=center colspan=3>$dedication</td>
    </tr>
    <tr>
        <td width=64 align=center id=bg1><b>전투</b></td>
        <td width=132 align=center>{$general['warnum']}</td>
        <td width=48 align=center id=bg1><b>계략</b></td>
        <td width=98 align=center>{$general['firenum']}</td>
        <td width=48 align=center id=bg1><b>사관</b></td>
        <td width=98 align=center>{$general['belong']}년</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>승률</b></td>
        <td align=center>{$general['winrate']} %</td>
        <td align=center id=bg1><b>승리</b></td>
        <td align=center>{$general['killnum']}</td>
        <td align=center id=bg1><b>패배</b></td>
        <td align=center>{$general['deathnum']}</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>살상률</b></td>
        <td align=center>{$general['killrate']} %</td>
        <td align=center id=bg1><b>사살</b></td>
        <td align=center>{$general['killcrew']}</td>
        <td align=center id=bg1><b>피살</b></td>
        <td align=center>{$general['deathcrew']}</td>
    </tr>
</table>
<table width=498 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr><td align=center colspan=3 id=bg1><b>숙 련 도</b></td></tr>
    <tr height=16>
        <td width=64 align=center id=bg1><b>보병</b></td>
        <td width=64>　　{$general['dex0']}</td>
        <td width=366 align=center>".bar($dex0, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>궁병</b></td>
        <td>　　{$general['dex10']}</td>
        <td align=center>".bar($dex10, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>기병</b></td>
        <td>　　{$general['dex20']}</td>
        <td align=center>".bar($dex20, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>귀병</b></td>
        <td>　　{$general['dex30']}</td>
        <td align=center>".bar($dex30, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>차병</b></td>
        <td>　　{$general['dex40']}</td>
        <td align=center>".bar($dex40, $skin, 16)."</td>
    </tr>
</table>";
}

function pushTrickLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_tricklog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushProcessLog($log) {
    $size = count($log);
    if($size > 0) {
        $date = date('Y_m_d');
        $fp = fopen("logs/_{$date}_processlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function delStepLog() {
    $date = date('Y_m_d');
    @unlink("logs/_{$date}_steplog.txt");
}

function pushStepLog($log) {
    $date = date('Y_m_d');
    $fp = fopen("logs/_{$date}_steplog.txt", "a");
    fwrite($fp, $log."\n");
    fclose($fp);
}

function pushLockLog($log) {
    $size = count($log);
    if($size > 0) {
        $date = date('Y_m_d');
        $fp = fopen("logs/_{$date}_locklog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushAdminLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_adminlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushAuctionLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_auctionlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushGenLog($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/gen{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushBatRes($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/batres{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushBatLog($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/batlog{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushAllLog($log) {
    $text = join("\n", $log)."\n";
    file_put_contents(__dir__.'/logs/_alllog.txt', $text, FILE_APPEND);
}

function pushHistory($history) {
    $text = join("\n", $history)."\n";
    file_put_contents(__dir__.'/logs/_history.txt', $text, FILE_APPEND);
}

function getRawLog($path, $count, $line_length, $skin){
    //TODO: tail과 유사한 형태로 처리할 수 있는게 나을 듯. 그 이전에 파일 로그는 좀... ㅜㅜ
    if(!file_exists($path)){
        return null;
    }

    $fp = fopen($path, 'r');
    @fseek(fp, -$count * $line_length, SEEK_END);
    $data = fread($fp, $count * $line_length);
    fclose($fp);
}

function TrickLog($count, $skin) {
    if(!file_exists("logs/_tricklog.txt")){
        return '';
    }
    $fp = @fopen("logs/_tricklog.txt", "r");
    @fseek($fp, -$count*150, SEEK_END);
    $file = @fread($fp, $count*150);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) { $str .= ConvertLog($log[count($log)-2-$i], $skin)."<br>"; }
    echo $str;
}

function AllLog($count, $skin) {
    if(!file_exists("logs/_alllog.txt")){
        return '';
    }
    $fp = @fopen("logs/_alllog.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
    	 $str .= isset($log[count($log)-2-$i]) ? ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
  	}
    echo $str;
}

function AuctionLog($count, $skin) {
    if(!file_exists("logs/_auctionlog.txt")){
        return '';
    }
    $fp = @fopen("logs/_auctionlog.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) { $str .= ConvertLog($log[count($log)-2-$i], $skin)."<br>"; }
    echo $str;
}

function History($count, $skin) {
    if(!file_exists("logs/_history.txt")){
        return '';
    }
    $fp = @fopen("logs/_history.txt", "r");
    @fseek($fp, -300*$count, SEEK_END); //
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
    	 $str .= isset($log[count($log)-2-$i]) ? ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
	}
    echo $str;
}

function MyLog($no, $count, $skin) {
    if(!file_exists("logs/gen{$no}.txt")){
        return '';
    }
    $fp = @fopen("logs/gen{$no}.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
    	 $str .= isset($log[count($log)-2-$i]) ? ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
	}
    echo $str;
}

function MyBatRes($no, $count, $skin) {
    if(!file_exists("logs/batres{$no}.txt")){
        return '';
    }
    $fp = @fopen("logs/batres{$no}.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
         $str .= isset($log[count($log)-2-$i]) ?  ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
    }
    echo $str;
}

function MyBatLog($no, $count, $skin) {
    if(!file_exists("logs/batlog{$no}.txt")){
        return '';
    }
    $fp = @fopen("logs/batlog{$no}.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
         $str .= isset($log[count($log)-2-$i]) ?  ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
    }
    echo $str;
}

function MyHistory($connect, $no, $skin) {
    $query = "select history from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    echo ConvertLog($general['history'], $skin);
}

function addHistory($me, $history) {
    DB::db()->query("update general set history=concat(%s, history) where no=%i",
        $history.'<br>', $me['no']);
}

function addNationHistory($nation, $history) {
    //FIXME: update 쿼리만으로도 구성 가능해보임.
    $nation['history'] = "{$nation['history']}{$history}<br>";
    DB::db()->query("update nation set history=concat(%s, history) where nation=%i",
        $history.'<br>', $nation['nation']);
}

function adminMsg($connect, $skin=1) {
    $query = "select msg from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    echo "운영자 메세지 : <font color="; echo $skin>0?"yellow":"white"; echo ">";
    echo $admin['msg']."</font>";
}

function getOnlineNum() {
    return DB::db()->queryFirstField('select `online` from `game` where `no`=1');
}

function onlinegen($connect) {
    $onlinegen = "";
    $generalID = getGeneralID();
    $nationID = DB::db()->queryFirstField('select `nation` from `general` where `no` = %i', $generalID);
    if($nationID !== null || Util::toInt($nationID) === 0) {
        $query = "select onlinegen from game where no='1'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $game = MYDB_fetch_array($result);

        $onlinegen = $game['onlinegen'];
    } else {
        $query = "select onlinegen from nation where nation='{$_SESSION['p_nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);

        $onlinegen = $nation['onlinegen'];
    }
    return $onlinegen;
}

function onlineNation($connect) {
    $query = "select onlinenation from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);
    return $game['onlinenation'];
}

function nationMsg($connect) {
    $query = "select no,nation,skin from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select msg from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    echo "<font color="; echo $me['skin']>0?"orange":"white"; echo ">".$nation['msg']."</font>";
}


function PushMsg($type, $nation, $picture, $imgsvr, $from, $fromcolor, $to, $tocolor, $msg) {
    if($nation == 0) { $nation = 0; }
    if($fromcolor == "") { $fromcolor = "#FFFFFF"; }
    if($to == "") { $to = "재야"; }
    if($tocolor == "") { $tocolor = "#FFFFFF"; }

    $date = date('Y-m-d H:i:s');
    if($type == 1) { $file = "_all_msg.txt"; }
    else { $file = "_nation_msg{$nation}.txt"; }
    $fp = fopen("logs/{$file}", "a");
    //로그 파일에 기록
    $str = "{$type}|".StringUtil::Fill($from,12," ")."|".StringUtil::Fill($to,12," ")."|".$date."|".$msg."|".$fromcolor."|".$tocolor."|".$picture."|".$imgsvr;
    fwrite($fp, "{$str}\n");
    fclose($fp);
}

function msgprint($connect, $msg, $name, $picture, $imgsvr, $when, $num, $type) {
    global $_basecolor2, $_basecolor4, $images;

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
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=64 align=center id=bg1><font size=1>$name</font></td>
        <td width=772 align=center id=bg1><font size=4><b>$message[0]</b></font></td>
        <td width=148 align=center id=bg1>$when</td>
    </tr>
    <tr>
        <td width=64 height=64 valign=top><img src={$imageTemp}/{$picture} width=64 height=64 border=0></td>
        <td width=932 colspan=2>$message[1]</td>
    </tr>";
    for($i=0; $i < $count; $i++) {
        $who = Tag2Code($message[2+$i*2]);
        $reply = Tag2Code($message[3+$i*2]);
        $query = "select name from general where no='$who'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $regen = MYDB_fetch_array($result);
        echo "
    <tr>
        <td width=64 align=center><font size=1>{$regen['name']}</font></td>
        <td width=932 colspan=2>$reply</td>
    </tr>";
    }
    echo "
    <tr>
        <form name=reply_form{$num} method=post action=$board>
        <td width=64 align=center>댓글달기</td>
        <td width=932 colspan=2>
            <input type=textarea name=reply maxlength=250 style=color:white;background-color:black;width:830;>
            <input type=submit value=댓글달기>
            <input type=hidden name=num value=$num>
        </td>
        </form>
    </tr>
</table>
<br>";
}

function banner() {

    return sprintf('<font size=2>%s / %s <br> %s</font>', GameConst::$version, GameConst::$banner, GameConst::$helper);
}

function addTurn($date, $turnterm=1) {
    $lastday = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

    $year  = $date[0].$date[1].$date[2].$date[3];
    $month = floor($date[5].$date[6]);
    $day   = $date[8].$date[9];
    $hour  = $date[11].$date[12];
    $min   = $date[14].$date[15];
    $sec   = $date[17].$date[18];

    //윤년계산
    if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) { $lastday[2] = 29; }

    if($turnterm == 0)     { $hour += 2; }
    elseif($turnterm == 1) { $hour += 1; }
    elseif($turnterm == 2) { $min += 30; }
    elseif($turnterm == 3) { $min += 20; }
    elseif($turnterm == 4) { $min += 10; }
    elseif($turnterm == 5) { $min +=  5; }
    elseif($turnterm == 6) { $min +=  2; }
    elseif($turnterm == 7) { $min +=  1; }

    if($min >= 60) { $min %= 60; $hour++; }
    if($hour >= 24) { $hour %= 24; $day++; }
    if($day > $lastday[$month]) { $month++; $day = 1; }
    if($month >= 13) { $year++; $month = 1; }

    $month = StringUtil::Fill2($month, 2, "0");
    $day   = StringUtil::Fill2($day,   2, "0");
    $hour  = StringUtil::Fill2($hour,  2, "0");
    $min   = StringUtil::Fill2($min,   2, "0");
    $sec   = StringUtil::Fill2($sec,   2, "0");

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
}

function add12Turn($date, $turnterm=1) {
    $lastday = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

    $year  = $date[0].$date[1].$date[2].$date[3];
    $month = floor($date[5].$date[6]);
    $day   = $date[8].$date[9];
    $hour  = $date[11].$date[12];
    $min   = $date[14].$date[15];
    $sec   = $date[17].$date[18];

    //윤년계산
    if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) { $lastday[2] = 29; }

    if($turnterm == 0)     { $day  +=  1; }
    elseif($turnterm == 1) { $hour += 12; }
    elseif($turnterm == 2) { $hour +=  6; }
    elseif($turnterm == 3) { $hour +=  4; }
    elseif($turnterm == 4) { $hour +=  2; }
    elseif($turnterm == 5) { $hour +=  1; }
    elseif($turnterm == 6) { $min  += 24; }
    elseif($turnterm == 7) { $min  += 12; }

    if($min >= 60) { $min %= 60; $hour++; }
    if($hour >= 24) { $hour %= 24; $day++; }
    if($day > $lastday[$month]) { $month++; $day = 1; }
    if($month >= 13) { $year++; $month = 1; }

    $month = StringUtil::Fill2($month, 2, "0");
    $day   = StringUtil::Fill2($day,   2, "0");
    $hour  = StringUtil::Fill2($hour,  2, "0");
    $min   = StringUtil::Fill2($min,   2, "0");
    $sec   = StringUtil::Fill2($sec,   2, "0");

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
}

function subTurn($date, $turnterm=1) {
    $lastday = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

    $year  = $date[0].$date[1].$date[2].$date[3];
    $month = floor($date[5].$date[6]);
    $day   = $date[8].$date[9];
    $hour  = $date[11].$date[12];
    $min   = $date[14].$date[15];
    $sec   = $date[17].$date[18];

    if($turnterm == 0)     { $hour -= 2; }
    elseif($turnterm == 1) { $hour -= 1; }
    elseif($turnterm == 2) { $min -= 30; }
    elseif($turnterm == 3) { $min -= 20; }
    elseif($turnterm == 4) { $min -= 10; }
    elseif($turnterm == 5) { $min -=  5; }
    elseif($turnterm == 6) { $min -=  2; }
    elseif($turnterm == 7) { $min -=  1; }

    //윤년계산
    if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) { $lastday[2] = 29; }

    if($min < 0) { $min = 60 + $min; $hour--; }
    if($hour < 0) { $hour = 24 + $hour; $day--; }
    if($day <= 0) { $month--; $day = $lastday[$month]; }
    if($month <= 0) { $year--; $month = 12; }

    $year  = StringUtil::Fill2($year,  2, "0");
    $month = StringUtil::Fill2($month, 2, "0");
    $day   = StringUtil::Fill2($day,   2, "0");
    $hour  = StringUtil::Fill2($hour,  2, "0");
    $min   = StringUtil::Fill2($min,   2, "0");
    $sec   = StringUtil::Fill2($sec,   2, "0");

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
}

function cutTurn($date, $turnterm=1) {
    //          0123456789012345678
    // $date = "2000-01-01 00:00:00";
    // 0 : 120분, 1 : 60분, 2 : 30분, 3 : 10분, 4 : 5분
    switch($turnterm) {
        case 0:
            $hour = $date[11].$date[12];
            if($hour % 2 == 1) { $hour--; }
            $date[11] = floor($hour / 10);
            $date[12] = $hour % 10;
            $date[14] = "0";
            $date[15] = "0";
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 1:
            $date[14] = "0";
            $date[15] = "0";
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 2:
            $min = $date[14].$date[15];
            if($min < 30) { $min = 0; }
            else { $min = 30; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 3:
            $min = $date[14].$date[15];
            if($min < 20) { $min = 0; }
            elseif($min < 40) { $min = 20; }
            else { $min = 40; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 4:
            $min = $date[14].$date[15];
            if($min < 10) { $min = 0; }
            elseif($min < 20) { $min = 10; }
            elseif($min < 30) { $min = 20; }
            elseif($min < 40) { $min = 30; }
            elseif($min < 50) { $min = 40; }
            else { $min = 50; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 5:
            $min = $date[14].$date[15];
            if($min < 5) { $min = 0; }
            elseif($min < 10) { $min = 5; }
            elseif($min < 15) { $min = 10; }
            elseif($min < 20) { $min = 15; }
            elseif($min < 25) { $min = 20; }
            elseif($min < 30) { $min = 25; }
            elseif($min < 35) { $min = 30; }
            elseif($min < 40) { $min = 35; }
            elseif($min < 45) { $min = 40; }
            elseif($min < 50) { $min = 45; }
            elseif($min < 55) { $min = 50; }
            else { $min = 55; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 6:
            $min = $date[14].$date[15];
            $min = floor($min / 2) * 2;
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 7:
            $date[17] = "0";
            $date[18] = "0";
            break;
    }

    return $date;
}

function CutDay($date) {
    $hour = $date[11].$date[12];
    if($hour >= 19) {
        $date[11] = "0";
        $date[12] = "1";

        $date = add12Turn($date, 1);
        $date = add12Turn($date, 1);
    } elseif($hour < 07) {
        $date[11] = "0";
        $date[12] = "1";
    } else {
        $date[11] = "1";
        $date[12] = "3";
    }
    $date[14] = "0";
    $date[15] = "0";
    $date[17] = "0";
    $date[18] = "0";
    return $date;
}

function increaseRefresh($type="", $cnt=1) {
    $date = date('Y-m-d H:i:s');

    DB::db()->query('UPDATE game set refresh=refresh+%i where `no`=1', $cnt);

    if(!Util::array_get($_SESSION['userID'], null)) {
        DB::db()->query('UPDATE general set `lastrefresh`=%s_date, `con`=`con`+%i_cnt, `connect`=`connect`+%i_cnt, refcnt=refcnt+%i_cnt, refresh=refresh+%i_cnt where `no`=%i_no',[
            'date'=>$date,
            'cnt'=>$cnt,
            'owner'->getGeneralID()
        ]);
    }

    $date = date('Y_m_d H:i:s');
    $date2 = substr($date, 0, 10);
    $online = getOnlineNum();
    $fp = fopen("logs/_{$date2}_refresh.txt", "a");
    $msg = StringUtil::Fill2($date,20," ").StringUtil::Fill2($_SESSION['userName'],13," ").StringUtil::Fill2($_SESSION['p_name'],13," ").StringUtil::Fill2($_SESSION['p_ip'],16," ").StringUtil::Fill2($type, 10, " ")." 동접자: {$online}";
    fwrite($fp, $msg."\n");
    fclose($fp);

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
        file_put_contents("logs/_{$date2}_ipcheck.txt",
            sprintf("ID:%s//name:%s//REMOTE_ADDR:%s%s\n",
                $_SESSION['userName'],$_SESSION['p_name'],$_SERVER['REMOTE_ADDR'],$str), FILE_APPEND);
    }
}

function updateTraffic() {
    $online = getOnlineNum();
    $db = DB::db();
    $game = $db->queryFirstRow('SELECT year,month,refresh,maxonline,maxrefresh from game where no=1');

    //최다갱신자
    $user = $db->queryFirstRow('select name,refresh from general order by refresh desc limit 1');

    if($game['maxrefresh'] < $game['refresh']) {
        $game['maxrefresh'] = $game['refresh'];
    }
    if($game['maxonline'] < $online) {
        $game['maxonline'] = $online;
    }
    $db->update('game',[
        'refresh'=>0,
        'maxrefresh'=>$game['maxrefresh'],
        'maxonline'=>$game['maxonline']
    ], 'no=1');

    $db->update('general', ['refresh'=>0]);

    $date = date('Y-m-d H:i:s');
    $fp = fopen("logs/_traffic.txt", "a");
    //일시|년|월|총갱신|접속자|최다갱신자
    $msg = StringUtil::Fill2($date,20," ")."|".StringUtil::Fill2($game['year'],3," ")."|".StringUtil::Fill2($game['month'],2," ")."|".StringUtil::Fill2($game['refresh'],8," ")."|".StringUtil::Fill2($online,5," ")."|".StringUtil::Fill2($user['name']."(".$user['refresh'].")",20," ");
    fwrite($fp, $msg."\n");
    fclose($fp);
}

function CheckOverhead($connect) {
    //서버정보
    $query = "select conweight,turnterm,conlimit from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $onlineNumber = getOnlineNum();
    switch($admin['turnterm']) {
    case 0: $thr1 =  30; $thr2 =  60; $thr3 = 120; $con1 = 480; $con2 = 360; $con3 = 240; $con4 = 120; break;   // 120분턴
    case 1: $thr1 =  30; $thr2 =  60; $thr3 = 120; $con1 = 480; $con2 = 360; $con3 = 240; $con4 = 120; break;   // 60분턴
    case 2: $thr1 =  10; $thr2 =  20; $thr3 =  30; $con1 = 360; $con2 = 240; $con3 = 120; $con4 =  60; break;   // 30분턴
    case 3: $thr1 =  10; $thr2 =  20; $thr3 =  30; $con1 = 240; $con2 = 180; $con3 = 120; $con4 =  60; break;   // 20분턴
    case 4: $thr1 =  10; $thr2 =  20; $thr3 =  30; $con1 = 240; $con2 = 180; $con3 = 120; $con4 =  60; break;   // 10분턴
    case 5: $thr1 =   5; $thr2 =  10; $thr3 =  20; $con1 = 120; $con2 =  90; $con3 =  60; $con4 =  30; break;   // 5분턴
    case 6: $thr1 =   5; $thr2 =  10; $thr3 =  20; $con1 =  90; $con2 =  60; $con3 =  40; $con4 =  30; break;   // 2분턴
    case 7: $thr1 =   5; $thr2 =  10; $thr3 =  20; $con1 =  90; $con2 =  60; $con3 =  40; $con4 =  30; break;   // 1분턴
    }

    $thr1 *= $admin['conweight'] / 100;
    $thr2 *= $admin['conweight'] / 100;
    $thr3 *= $admin['conweight'] / 100;
    $con1 *= $admin['conweight'] / 100;
    $con2 *= $admin['conweight'] / 100;
    $con3 *= $admin['conweight'] / 100;
    $con4 *= $admin['conweight'] / 100;

    //if($onlineNumber > $thr2)  { $me['map']  = 1; }
    if      ($onlineNumber > $thr3  && $admin['conlimit'] != $con4) {
        $query = "update game set conlimit='$con4' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($onlineNumber > $thr2  && $admin['conlimit'] != $con3) {
        $query = "update game set conlimit='$con3' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($onlineNumber > $thr1  && $admin['conlimit'] != $con2) {
        $query = "update game set conlimit='$con2' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($onlineNumber <= $thr1 && $admin['conlimit'] != $con1) {
        $query = "update game set conlimit='$con1' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
    }
}

function isLock() {
    return DB::db()->queryFirstField("select plock from plock where no=1") != 0;
}

function tryLock() {
    //NOTE: 게임 로직과 관련한 모든 insert, update 함수들은 lock을 거칠것을 권장함.
    $db = DB::db();
    //테이블 락
    $db->query("lock tables plock write");
    // 잠금
    $isUnlocked = $db->queryFirstField("select plock from plock where no=1") != 0;
    if($isUnlocked){
        $db->query("update plock set plock=1 where no=1");
    }
    
    //테이블 언락
    $db->query("unlock tables");

    return $isUnlocked;
}

function unlock() {
    // 풀림
    //NOTE: unlock에는 table lock이 필요없는가?
    DB::db()->query("update plock set plock=0 where no=1");
}

function timeover($connect) {
    $query = "select turnterm,TIMESTAMPDIFF(SECOND,turntime,now()) as diff from game where no=1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    switch($admin['turnterm']) {
    case 0: $t = 20; break;   // 120분턴
    case 1: $t = 10; break;   // 60분턴
    case 2: $t = 10; break;   // 30분턴
    case 3: $t = 10; break;   // 20분턴
    case 4: $t = 10; break;   // 10분턴
    case 5: $t =  5; break;   // 5분턴
    case 6: $t =  2; break;   // 2분턴
    case 7: $t =  1; break;   // 1분턴
    }

    $term = $admin['diff'];
    if($term >= $t || $term < 0) { return 1; }
    else { return 0; }
}

function checkDelay($connect) {
    //서버정보
    $query = "select turnterm,now() as now,TIMESTAMPDIFF(MINUTE,turntime,now()) as offset from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    // 1턴이상 갱신 없었으면 서버 지연
    switch($admin['turnterm']) {
        case 0: $term = 120; $threshold = 1; break;
        case 1: $term = 60;  $threshold = 1; break;
        case 2: $term = 30;  $threshold = 1; break;
        case 3: $term = 20;  $threshold = 1; break;
        case 4: $term = 10;  $threshold = 2; break;
        case 5: $term = 5;   $threshold = 3; break;
        case 6: $term = 2;   $threshold = 3; break;
        case 7: $term = 1;   $threshold = 3; break;
    }
    //지연 해야할 밀린 턴 횟수
    $iter = floor($admin['offset'] / $term);
    if($iter > $threshold) {
        $minute = $iter * $term;
        $query = "update game set turntime=DATE_ADD(turntime, INTERVAL $minute MINUTE),starttime=DATE_ADD(starttime, INTERVAL $minute MINUTE),tnmt_time=DATE_ADD(tnmt_time, INTERVAL $minute MINUTE)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set turntime=DATE_ADD(turntime, INTERVAL $minute MINUTE) where turntime<=DATE_ADD(turntime, INTERVAL $term MINUTE)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update auction set expire=DATE_ADD(expire, INTERVAL $minute MINUTE)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function updateOnline($connect) {
    //국가별 이름 매핑
    foreach(getAllNationStaticInfo() as $nation) {
        $nationname[$nation['nation']] = $nation['name'];
    }
    $nationname[0] = "재야";

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
	            $query = "update game set onlinegen='$onnation[0]' where no='1'";
	            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
	        } else {
	            $query = "update nation set onlinegen='$onnation[$key]' where nation='$key'";
	            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
	        }
	    }
	}

    //접속중인 국가
    $query = "update game set online='$onlinenum',onlinenation='$onnationstr' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function checkTurn($connect) {
    // 잦은 갱신 금지 현재 10초당 1회
    if(!timeover($connect)) { return; }
    // 현재 처리중이면 접근 불가

    // 파일락 획득
    //FIXME:이미 DB 테이블로 lock을 시도하는데 이게 따로 필요한가?
    $fp = fopen('lock.txt', 'r');
    if(!flock($fp, LOCK_EX)) {
         return; 
        }

    if(!tryLock()){
        return;
    }

    $locklog[0] = "- checkTurn()      : ".date('Y-m-d H:i:s')." : ".$_SESSION['userName'];
    pushLockLog($locklog);

    // 파일락 해제
    if(!flock($fp, LOCK_UN)) { return; }
    // 세마포어 해제
    //if(!@sem_release($sema)) { echo "치명적 에러! 유기체에게 문의하세요!"; exit(1); }

    $locklog[0] = "- checkTurn() 입   : ".date('Y-m-d H:i:s')." : ".$_SESSION['userName'];
    pushLockLog($locklog);
    
    //if(STEP_LOG) delStepLog();
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', 진입');
    
    //천통시에는 동결
    $query = "select turntime from game where isUnited=2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $down = MYDB_num_rows($result);
    if($down > 0) {
        $query = "update plock set plock=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return;
    }
    // 1턴이상 갱신 없었으면 서버 지연
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', checkDelay');
    checkDelay($connect);
    // 접속자수, 접속국가, 국가별 접속장수 갱신
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', checkDelay');
    updateOnline($connect);
    //접속자 수 따라서 갱신제한 변경
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', CheckOverhead');
    CheckOverhead($connect);
    //서버정보
    $query = "select * from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $date = date('Y-m-d H:i:s');
    // 최종 처리 월턴의 다음 월턴시간 구함
    $prevTurn = cutTurn($admin['turntime'], $admin['turnterm']);
    $nextTurn = addTurn($prevTurn, $admin['turnterm']);
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
            if($general['npc'] >= 2) { processAI($connect, $general['no']); }    // npc AI 처리
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', PreprocessCommand');
            PreprocessCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processCommand');
            processCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateCommand');
            updateCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTurntime');
            updateTurntime($connect, $general['no']);
            
        }
        
        // 트래픽 업데이트
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTraffic');
        updateTraffic();
        // 1달마다 처리하는 것들, 벌점 감소 및 건국,전턴,합병 -1, 군량 소모
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', preUpdateMonthly');
        $result = preUpdateMonthly($connect);
        if($result == false) {
            $locklog[0] = "-- checkTurn() 오류출 : ".date('Y-m-d H:i:s')." : ".$_SESSION['userName'];
            pushLockLog($locklog);

            // 잡금 해제
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', unlock');
            unlock();
            return false;
        }

        // 그 시각 년도,월 저장
        $dt = turnDate($connect, $nextTurn);
        $admin['year'] = $dt[0]; $admin['month'] = $dt[1];

        $locklog[0] = "-- checkTurn() ".$admin['month']."월 : ".date('Y-m-d H:i:s')." : ".$_SESSION['userName'];
        pushLockLog($locklog);

        // 이벤트 핸들러 동작
        foreach (DB::db()->query('SELECT * from event') as $rawEvent) {
            $eventID = $rawEvent['id'];
            $cond = Json::decode($rawEvent['cond']);
            $action = Json::decode($rawEvent['action']);
            $event = new Event\EventHandler($cond, $action);

            $event->tryRunEvent(array_merge([
                'currentEventID'=>$eventID
            ], $admin));
        }

        // 분기계산. 장수들 턴보다 먼저 있다면 먼저처리
        if($admin['month'] == 1) {
            // NPC 등장
            //if($admin['scenario'] > 0 && $admin['scenario'] < 20) { RegNPC($connect); }
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processGoldIncome');
            processGoldIncome($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processSpring');
            processSpring($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateYearly');
            updateYearly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', tradeRate');
            tradeRate($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', addAge');
            addAge($connect);
            // 새해 알림
            $alllog[] = "<C>◆</>{$admin['month']}월:<C>{$admin['year']}</>년이 되었습니다.";
            pushAllLog($alllog);
        } elseif($admin['month'] == 4) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
        } elseif($admin['month'] == 7) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processRiceIncome');
            processRiceIncome($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processFall');
            processFall($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', tradeRate');
            tradeRate($connect);
        } elseif($admin['month'] == 10) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
        }
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', postUpdateMonthly');
        postUpdateMonthly($connect);

        // 다음달로 넘김
        $prevTurn = $nextTurn;
        $nextTurn = addTurn($prevTurn, $admin['turnterm']);
    }

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
        
    // 이시각 정각 시까지 업데이트 완료했음
    $query = "update game set turntime='$prevTurn' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 그 시각 년도,월 저장
    $dt = turnDate($connect, $prevTurn);
    $admin['year'] = $dt[0]; $admin['month'] = $dt[1];
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
            if($general['npc'] >= 2) { processAI($connect, $general['no']); }    // npc AI 처리
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', PreprocessCommand');
            PreprocessCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processCommand');
            processCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateCommand');
            updateCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTurntime');
            updateTurntime($connect, $general['no']);
        }
    } while($gencount > 0);

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
    
    $query = "update game set turntime='$date' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 3턴 전 시간
    $letterdate = $date;
    $letterdate = subTurn($letterdate, $admin['turnterm']);
    $letterdate = subTurn($letterdate, $admin['turnterm']);
    $letterdate = subTurn($letterdate, $admin['turnterm']);
    //기한 지난 외교 메세지 지움(3개월 유지)
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
    for($i=0; $i < 5; $i++) {
        $query = "update nation set dip{$i}='',dip{$i}_who='0',dip{$i}_type='0',dip{$i}_when='' where dip{$i}_when < '$letterdate'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    // 부상 과도 제한
    $query = "update general set injury='80' where injury>'80'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //토너먼트 처리
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processTournament');
    processTournament($connect);
    //거래 처리
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAuction');
    processAuction($connect);
    // 잡금 해제
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', unlock');
    unlock();

    $locklog[0] = "- checkTurn()   출 : ".date('Y-m-d H:i:s')." : ".$_SESSION['userName'];
    pushLockLog($locklog);

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', finish');
    
    return true;
}

function addAge($connect) {
    //나이와 호봉 증가
    $query = "update general set age=age+1,belong=belong+1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    if($admin['year'] >= $admin['startyear']+3) {
        $query = "select no,name,nation,leader,power,intel from general where specage<=age and special='0'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            $special = getSpecial($connect, $general['leader'], $general['power'], $general['intel']);
            $query = "update general set special='$special' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>특기 【<b><L>".getGenSpecial($special)."</></b>】(을)를 익혔습니다!";
            addHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special)."</></b>】(을)를 습득");
            pushGenLog($general, $log);
        }

        $query = "select no,name,nation,leader,power,intel,npc,dex0,dex10,dex20,dex30,dex40 from general where specage2<=age and special2='0'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            $special2 = getSpecial2($connect, $general['leader'], $general['power'], $general['intel'], 0, $general['dex0'], $general['dex10'], $general['dex20'], $general['dex30'], $general['dex40']);

            $query = "update general set special2='$special2' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>특기 【<b><L>".getGenSpecial($special2)."</></b>】(을)를 익혔습니다!";
            addHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special2)."</></b>】(을)를 습득");
            pushGenLog($general, $log);
        }
    }
}

function turnDate($connect, $curtime) {
    $query = "select startyear,starttime,turnterm,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $turn = $admin['starttime'];
    $curturn = cutTurn($curtime, $admin['turnterm']);
    $num = 0;
    switch($admin['turnterm']) {
        case 0: $term = 7200; break;
        case 1: $term = 3600; break;
        case 2: $term = 1800; break;
        case 3: $term = 1200; break;
        case 4: $term = 600; break;
        case 5: $term = 300; break;
        case 6: $term = 120; break;
        case 7: $term = 60; break;
    }
    $num = floor((strtotime($curturn) - strtotime($turn)) / $term);

    $year = $admin['startyear'] + floor($num / 12);
    $month = 1 + (12+$num) % 12;

    // 바뀐 경우만 업데이트
    if($admin['month'] != $month || $admin['year'] != $year) {
        $query = "update game set year='$year',month='$month' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    $value[0] = $year;
    $value[1] = $month;
    return $value;
}


function triggerTournament($connect) {
    $query = "select tournament,tnmt_trig from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //현재 토너먼트 없고, 자동개시 걸려있을때, 40%확률
    if($admin['tournament'] == 0 && $admin['tnmt_trig'] > 0 && rand() % 100 < 40) {
        $type = rand() % 6; //  0 : 전력전, 1 : 통솔전, 2 : 일기토, 3 : 설전
        //전력전 50%, 통, 일, 설 각 17%
        if($type > 3) { $type = 0; }
        startTournament($connect, $admin['tnmt_trig'], $type);
    }
}

function PreprocessCommand($connect, $no) {
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

            $log[0] = "<C>●</><C>의술</>을 펼쳐 스스로 치료합니다!";
            pushGenLog($general, $log);
        }
            
        $query = "select no,name,injury from general where city='{$general['city']}' and injury>10 order by rand()";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $patientCount = MYDB_num_rows($result);
    
        if($patientCount > 0) {
            // 50% 확률로 치료
            $patientCount = round($patientCount * 0.5);
    
            $patientName = "";
            for($i=0; $i < $patientCount; $i++) {
                $patient = MYDB_fetch_array($result);
    
                //부상 치료
                $query = "update general set injury=0 where no='{$patient['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    
                $log[0] = "<C>●</><Y>{$general['name']}</>(이)가 <C>의술</>로써 치료해줍니다!";
                pushGenLog($patient, $log);
                
                if($patientName == "") {
                    $patientName = $patient['name'];
                }
            }

            if($patientCount == 1) {
                $log[0] = "<C>●</><C>의술</>을 펼쳐 도시의 장수 <Y>{$patientName}</>(을)를 치료합니다!";
            } else {
                $patientCount -= 1;
                $log[0] = "<C>●</><C>의술</>을 펼쳐 도시의 장수들 <Y>{$patientName}</> 외 <C>{$patientCount}</>명을 치료합니다!";
            }
            pushGenLog($general, $log);
        }
    }
    
    if($general['injury'] > 0) {
        if($general['item'] >=7 && $general['item'] <= 11) {
            //영구약 사용
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용하여 치료합니다!";
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


function updateTurntime($connect, $no) {
    $query = "select year,month,isUnited,turnterm from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

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

            $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name2']}</>(이)가 <Y>{$general['name']}</>의 육체에서 <S>유체이탈</>합니다!";
            pushAllLog($alllog);

            if($admin['isUnited'] == 0) {
                CheckHall($connect, $no);
            }
        } else {
            // 군주였으면 유지 이음
            if($general['level'] == 12) {
                nextRuler($connect, $general);
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
            if($gencount < 10) $gencount = 10;

            $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 병, 요절, 객사, 번개, 사채, 일확천금, 호랑이, 곰, 수영, 처형, 발견
            switch(rand()%42) {
            case 0:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 역병에 걸려 <R>죽고</> 말았습니다."; break;
            case 1:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>요절</>하고 말았습니다."; break;
            case 2:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 거리에서 갑자기 <R>객사</>하고 말았습니다."; break;
            case 3:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 안타깝게도 번개에 맞아 <R>죽고</> 말았습니다."; break;
            case 4:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 고리대금에 시달리다가 <R>자살</>하고 말았습니다."; break;
            case 5:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 일확천금에 놀라 심장마비로 <R>죽고</> 말았습니다."; break;
            case 6:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 산속에서 호랑이에게 물려 <R>죽고</> 말았습니다."; break;
            case 7:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 산책중 곰에게 할퀴어 <R>죽고</> 말았습니다."; break;
            case 8:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 수영을 하다 <R>익사</>하고 말았습니다."; break;
            case 9:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 황제를 모독하다가 <R>처형</>당하고 말았습니다."; break;
            case 10: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 이튿날 침실에서 <R>죽은채로</>발견되었습니다."; break;
            case 11: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 색에 빠져 기력이 쇠진해 <R>죽고</>말았습니다."; break;
            case 12: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 미녀를 보고 심장마비로 <R>죽고</>말았습니다."; break;
            case 13: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 우울증에 걸려 <R>자살</>하고 말았습니다."; break;
            case 14: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 천하 정세를 비관하며 <R>분신</>하고 말았습니다."; break;
            case 15: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 어떤 관심도 못받고 쓸쓸히 <R>죽고</>말았습니다."; break;
            case 16: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 유산 상속 문제로 다투다가 <R>살해</>당했습니다."; break;
            case 17: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 누군가의 사주로 자객에게 <R>암살</>당했습니다."; break;
            case 18: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 바람난 배우자에게 <R>독살</>당하고 말았습니다."; break;
            case 19: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 농약을 술인줄 알고 마셔 <R>죽고</>말았습니다."; break;
            case 20: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 아무 이유 없이 <R>죽고</>말았습니다."; break;
            case 21: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 전재산을 잃고 화병으로 <R>죽고</>말았습니다."; break;
            case 22: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 단식운동을 하다가 굶어 <R>죽고</>말았습니다."; break;
            case 23: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 귀신에게 홀려 시름 앓다가 <R>죽고</>말았습니다."; break;
            case 24: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 사람들에게 집단으로 맞아서 <R>죽고</>말았습니다."; break;
            case 25: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 갑자기 성벽에서 뛰어내려 <R>죽고</>말았습니다."; break;
            case 26: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 농사중 호미에 머리를 맞아 <R>죽고</>말았습니다."; break;
            case 27: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 저세상이 궁금하다며 <R>자살</>하고 말았습니다."; break;
            case 28: $alllog[0] = "<C>●</>{$admin['month']}월:운좋기로 소문난 <Y>{$general['name']}</>(이)가 불운하게도 <R>죽고</>말았습니다."; break;
            case 29: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 무리하게 단련을 하다가 <R>죽고</>말았습니다."; break;
            case 30: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 생활고를 비관하며 <R>자살</>하고 말았습니다."; break;
            case 31: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 평생 결혼도 못해보고 <R>죽고</> 말았습니다."; break;
            case 32: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 과식하다 배가 터져 <R>죽고</> 말았습니다."; break;
            case 33: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 웃다가 숨이 넘어가 <R>죽고</> 말았습니다."; break;
            case 34: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 추녀를 보고 놀라서 <R>죽고</> 말았습니다."; break;
            case 35: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 물에 빠진 사람을 구하려다 같이 <R>죽고</> 말았습니다."; break;
            case 36: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 독살을 준비하다 독에 걸려 <R>죽고</> 말았습니다."; break;
            case 37: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 뒷간에서 너무 힘을 주다가 <R>죽고</> 말았습니다."; break;
            case 38: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 돌팔이 의사에게 치료받다가 <R>죽고</> 말았습니다."; break;
            case 39: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 남의 보약을 훔쳐먹다 부작용으로 <R>죽고</> 말았습니다."; break;
            case 40: $alllog[0] = "<C>●</>{$admin['month']}월:희대의 사기꾼 <Y>{$general['name']}</>(이)가 <R>사망</>했습니다."; break;
            case 41: $alllog[0] = "<C>●</>{$admin['month']}월:희대의 호색한 <Y>{$general['name']}</>(이)가 <R>사망</>했습니다."; break;
            default: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>사망</>했습니다."; break;
            }
            // 엔피씨,엠피씨,의병 사망로그
            if($general['npc'] == 2) {
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>사망</>했습니다.";
            } elseif($general['npc'] >= 3) {
                switch(rand()%10) {
                case 0: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 푸대접에 실망하여 떠났습니다."; break;
                case 1: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 갑자기 화를 내며 떠났습니다."; break;
                case 2: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 의견차이를 좁히지 못하고 떠났습니다."; break;
                case 3: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 판단 착오였다며 떠났습니다."; break;
                case 4: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 생활고가 나아지지 않는다며 떠났습니다."; break;
                case 5: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 기대가 너무 컸다며 떠났습니다."; break;
                case 6: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 아무 이유 없이 떠났습니다."; break;
                case 7: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 자기 목적은 달성했다며 떠났습니다."; break;
                case 8: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 자기가 없어도 될것 같다며 떠났습니다."; break;
                case 9: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 처자식이 그립다며 떠났습니다."; break;
                }
            }

            pushAllLog($alllog);

            return;
        }
    }

    if($general['age'] >= 80 && $general['npc'] == 0) {
        if($admin['isUnited'] == 0) {
            CheckHall($connect, $no);
        }

        $query = "update general set leader=leader*0.85,power=power*0.85,intel=intel*0.85,injury=0,experience=experience*0.5,dedication=dedication*0.5,firenum=0,warnum=0,killnum=0,deathnum=0,killcrew=0,deathcrew=0,age=20,specage=0,specage2=0,crew=crew*0.85,dex0=dex0*0.5,dex10=dex10*0.5,dex20=dex20*0.5,dex30=dex30*0.5,dex40=dex40*0.5 where no='$no'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>은퇴</>하고 그 자손이 유지를 이어받았습니다.";
        pushAllLog($alllog);

        $log[0] = "<C>●</>나이가 들어 <R>은퇴</>하고 자손에게 자리를 물려줍니다.";
        pushGenLog($general, $log);
        addHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:나이가 들어 은퇴하고, 자손에게 관직을 물려줌");
    }

    $turntime = addTurn($general['turntime'], $admin['turnterm']);

    $query = "update general set turntime='$turntime' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function CheckHall($connect, $no) {
    $type = array(
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

    $query = "select name,nation,picture,
        experience,dedication,warnum,firenum,killnum,
        killnum/warnum*10000 as winrate,killcrew,killcrew/deathcrew*10000 as killrate,
        dex0,dex10,dex20,dex30,dex40,
        ttw/(ttw+ttd+ttl)*10000 as ttrate, ttw+ttd+ttl as tt,
        tlw/(tlw+tld+tll)*10000 as tlrate, tlw+tld+tll as tl,
        tpw/(tpw+tpd+tpl)*10000 as tprate, tpw+tpd+tpl as tp,
        tiw/(tiw+tid+til)*10000 as tirate, tiw+tid+til as ti,
        betgold, betwin, betwingold, betwingold/betgold*10000 as betrate
        from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    for($k=0; $k < 21; $k++) {
        $query = "select * from hall where type='$k' order by data desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);

        //승률,살상률인데 10회 미만 전투시 스킵
        if(($k == 5 || $k == 7) && $general['warnum']<10) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 13 && $general['tt'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 14 && $general['tl'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 15 && $general['tp'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 16 && $general['ti'] < 50) { continue; }
        //수익률인데 1000미만시 스킵
        if($k == 20 && $general['betgold'] < 1000) { continue; }

        $rank = 10;
        for($i=0; $i < $count; $i++) {
            $ranker = MYDB_fetch_array($result);

            if($general[$type[$k]] >= $ranker['data']) {
                $rank = $i;
                break;
            }
        }
        for($i=8; $i >= $rank; $i--) {
            $j = $i + 1;
            $query = "select * from hall where type='$k' and rank='$i'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $ranker = MYDB_fetch_array($result);

            $query = "update hall set name='{$ranker['name']}', nation='{$ranker['nation']}', data='{$ranker['data']}', color='{$ranker['color']}', picture='{$ranker['picture']}' where type='$k' and rank='$j'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        $query = "update hall set name='{$general['name']}', nation='{$nation['name']}', data='{$general[$type[$k]]}', color='{$nation['color']}', picture='{$general['picture']}' where type='$k' and rank='$rank'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}


function uniqueItem($connect, $general, $log, $vote=0) {
    if($general['npc'] >= 2 || $general['betray'] > 1) { return $log; }
    if($general['weap'] > 6 || $general['book'] > 6 || $general['horse'] > 6 || $general['item'] > 6) { return $log; }

    $query = "select year,month,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);

    $query = "select count(*) as cnt from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if($game['scenario'] == 0)  { $prob = $gen['cnt'] * 5; }  // 5~6개월에 하나씩 등장
    else { $prob = $gen['cnt']; }  // 1~2개월에 하나씩 등장

    if($vote == 1) { $prob = round($gen['cnt'] * 0.7 / 3); }     // 투표율 70%, 투표 한번에 2~3개 등장
    elseif($vote == 2) { $prob = round($gen['cnt'] / 10 / 2); }   // 랜임시 2개(10%) 등장(200명중 20명 랜임시도?)
    elseif($vote == 3) { $prob = round($gen['cnt'] / 10 / 4); }   // 건국시 4개(20%) 등장(200명시 20국 정도 됨)

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
                if($occupied[$i] == 0) {
                    $item[count($item)] = $i;
                }
            }
            $it = $item[rand() % count($item)];

            $query = "update general set {$type}='$it' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation = getNationStaticInfo($general['nation']);

            if($nation == null) {
                $nation['name'] = "재야";
            }

            switch($sel) {
            case 0:
                $log[] = "<C>●</><C>".getWeapName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                addHistory($general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getWeapName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                }
                break;
            case 1:
                $log[] = "<C>●</><C>".getBookName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                addHistory($general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getBookName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                }
                break;
            case 2:
                $log[] = "<C>●</><C>".getHorseName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                addHistory($general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getHorseName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                }
                break;
            case 3:
                $log[] = "<C>●</><C>".getItemName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                addHistory($general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getItemName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                }
                break;
            }
            pushAllLog($alllog);
            pushHistory($history);
        }
    }
    return $log;
}

function checkAbility($connect, $general, $log) {

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

function checkDedication($connect, $general, $log) {
    $dedlevel = getDedLevel($general['dedication']);

    $query = "update general set dedlevel='$dedlevel' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 승급했다면
    if($general['dedlevel'] < $dedlevel) {
        $log[] = "<C>●</><Y>".getDed($general['dedication'])."</>(으)로 <C>승급</>하여 봉록이 <C>".getBill($general['dedication'])."</>(으)로 <C>상승</>했습니다!";
    // 강등했다면
    } elseif($general['dedlevel'] > $dedlevel) {
        $log[] = "<C>●</><Y>".getDed($general['dedication'])."</>(으)로 <R>강등</>되어 봉록이 <C>".getBill($general['dedication'])."</>(으)로 <R>하락</>했습니다!";
    }

    return $log;
}

function checkExperience($connect, $general, $log) {
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

function getAdmin($connect) {
    $query = "select * from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    return $admin;
}

function getMe($connect) {
    $query = "select * from general where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error("접속자가 많아 접속을 중단합니다. 잠시후 갱신해주세요.<br>getMe : ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    return $me;
}

function getTroop($connect, $troop) {
    $query = "select * from troop where troop='$troop'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $troop = MYDB_fetch_array($result);

    return $troop;
}

function getCity($connect, $city, $sel="*") {
    $query = "select {$sel} from city where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    return $city;
}

function getNation($connect, $nation) {
    $query = "select * from nation where nation='$nation'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    return $nation;
}

function deleteNation($connect, $general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【멸망】</b></><D><b>{$nation['name']}</b></>은(는) <R>멸망</>했습니다.";

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

    pushHistory($history);
    refreshNationStaticInfo();
}

function nextRuler($connect, $general) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

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
            DeleteConflict($connect, $general['nation']);
            deleteNation($connect, $general);
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

    $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【유지】</b></><Y>{$nextruler['name']}</>(이)가 <D><b>{$nation['name']}</b></>의 유지를 이어 받았습니다";

    pushHistory($history);
    addNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【유지】</b></><Y>{$nextruler['name']}</>(이)가 <D><b>{$nation['name']}</b></>의 유지를 이어 받음.");
    // 장수 삭제 및 부대처리는 checkTurn에서
}


function distance($connect, $from, $maxDist=99) {
    include_once("queue.php");

    $query = "select city,path from city";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityNum = MYDB_num_rows($result);
    for($i=0; $i < $cityNum; $i++) {
        $city = MYDB_fetch_array($result);
        $cityPath[$city['city']] = $city['path'];
        $dist[$city['city']] = 99;
    }

    $select = 0;
    $queue = new Queue(20);
    $queue2 = new Queue(20);
    $q = $queue;
    $q2 = $queue2;
    $distance = $dist[$from] = 0;
    $q->push($from);
    while($q->getSize() > 0 || $q2->getSize() > 0) {
        $distance++;
        if($distance > $maxDist) return $dist;
        while($q->getSize() > 0) {
            $city = $q->pop();
            unset($path);
            $path = explode("|", $cityPath[$city]);
            for($i=0; $i < count($path); $i++) {
                if($dist[$path[$i]] > $distance) {
                    $dist[$path[$i]] = $distance;
                    $q2->push($path[$i]);
                }
            }
        }
        if($select == 0) {
            $q2 = $queue;
            $q = $queue2;
        } else {
            $q = $queue;
            $q2 = $queue2;
        }
        $select = 1 - $select;
    }

    return $dist;
}

function isClose($connect, $nation1, $nation2) {
    $isClose = 0;
    // $nation1의 모든 도시
    $query = "select path from city where nation='$nation1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    // 국가의 모든 도시 검색
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        // 각 도시당 모든 인접 도시 플래그 세팅
        $path = explode("|", $city['path']);
        for($j=0; $j < count($path); $j++) {
            $barrier[$path[$j]] = 1;
        }
    }

    // $nation2의 모든 도시 선택
    $query = "select city from city where nation='$nation2'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        if($barrier[$city['city']] == 1) {
            $isClose = 1;
        }
    }

    return $isClose;
}

function CharExperience($exp, $personal) {
    switch($personal) {
        case  0:    case  1;    case  6:
            $exp *= 1.1; break;
        case  4:    case  5:    case  7:    case 10:
            $exp *= 0.9; break;
    }
    $exp = round($exp);

    return $exp;
}

function CharDedication($ded, $personal) {
    switch($personal) {
        case 10:
            $ded *= 0.9; break;
    }
    $ded = round($ded);

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

function TrickInjury($connect, $city, $type=0) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

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
            $injury = floor($injury / 2) + 1;   // 부상 1~16

            $query = "update general set crew=crew*0.98,atmos=atmos*0.98,train=train*0.98,injury=injury+'$injury' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($general, $log);
        }
    }
}

function getRandTurn($term) {
    switch($term) {
        case 0: $randtime = rand() % 7200; break;
        case 1: $randtime = rand() % 3600; break;
        case 2: $randtime = rand() % 1800; break;
        case 3: $randtime = rand() % 1200; break;
        case 4: $randtime = rand() % 600; break;
        case 5: $randtime = rand() % 300; break;
        case 6: $randtime = rand() % 120; break;
        case 7: $randtime = rand() % 60; break;
        default:$randtime = rand() % 3600; break;
    }

    $turntime = date('Y-m-d H:i:s', strtotime('now') + $randtime);

    return $turntime;
}

function getRandTurn2($term) {
    switch($term) {
        case 0: $randtime = rand() % 7200; break;
        case 1: $randtime = rand() % 3600; break;
        case 2: $randtime = rand() % 1800; break;
        case 3: $randtime = rand() % 1200; break;
        case 4: $randtime = rand() % 600; break;
        case 5: $randtime = rand() % 300; break;
        case 6: $randtime = rand() % 120; break;
        case 7: $randtime = rand() % 60; break;
        default:$randtime = rand() % 3600; break;
    }

    $turntime = date('Y-m-d H:i:s', strtotime('now') - $randtime);

    return $turntime;
}
