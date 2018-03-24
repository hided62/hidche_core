<?php
namespace sammo;


/**
 * 템플릿 생성과 관련된 함수들을 모아두는 파일
 * 
 * NOTE: 아직 converter와 명확한 구분이 되어있지 않음.
 * TODO: legacy template들을 전부 template 폴더로 모아둘 필요 있음
 * TODO: side effect를 제거
 */


function turnTable() {
    return "
<select name=turn[] size=11 multiple style=width:50px;color:white;background-color:black;font-size:13px;>
    <option value=100>전체</option>
    <option value=99>홀턴</option>
    <option value=98>짝턴</option>
    <option selected value=0> 1턴</option>
    <option value=1> 2턴</option>
    <option value=2> 3턴</option>
    <option value=3> 4턴</option>
    <option value=4> 5턴</option>
    <option value=5> 6턴</option>
    <option value=6> 7턴</option>
    <option value=7> 8턴</option>
    <option value=8> 9턴</option>
    <option value=9>10턴</option>
    <option value=10>11턴</option>
    <option value=11>12턴</option>
    <option value=12>13턴</option>
    <option value=13>14턴</option>
    <option value=14>15턴</option>
    <option value=15>16턴</option>
    <option value=16>17턴</option>
    <option value=17>18턴</option>
    <option value=18>19턴</option>
    <option value=19>20턴</option>
    <option value=20>21턴</option>
    <option value=21>22턴</option>
    <option value=22>23턴</option>
    <option value=23>24턴</option>
</select>
";
}

function CoreTurnTable() {
    return "
<select name=turn[] size=3 multiple style=color:white;background-color:black;font-size:13px;>
    <option selected value=0> 1턴</option>
    <option value=1> 2턴</option>
    <option value=2> 3턴</option>
    <option value=3> 4턴</option>
    <option value=4> 5턴</option>
    <option value=5> 6턴</option>
    <option value=6> 7턴</option>
    <option value=7> 8턴</option>
    <option value=8> 9턴</option>
    <option value=9>10턴</option>
    <option value=10>11턴</option>
    <option value=11>12턴</option>
</select>
";
}


function allButton() {
    
    global $_basecolor2;
    $npcmode = getDB()->queryFirstField("select npcmode from game where no='1'");
    if($npcmode == 1) {
        $site = "a_npcList.php";
        $call = "빙의일람";
    } else {
        $site = "a_vote.php";
        $call = "설문조사";
    }

    $templates = new League\Plates\Engine('templates');

    return $templates->render('allButton', [
        'call' => $call,
        'site' => $site
    ]);
}


function commandButton() {
    global $_basecolor, $_basecolor2;

    $generalID = getGeneralID();
    
    if($generalID === null){
        return '';
    }
    $db = getDB();
    
    $me = $db->queryFirstRow("select skin,no,nation,level,belong from general where no=%i", $generalID);

    $nation = $db->queryFirstRow("select nation,color,secretlimit from nation where nation=%i",$me['nation']);

    if($nation['color'] == "") { $nation['color'] = "000000"; }

    $result = '';
    $result .= "
<table align=center border=0 cellspacing=0 cellpadding=0 style=font-size:13px;word-break:break-all; id=bg2>
    <tr>";

    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='회 의 실' onclick='refreshing(1,1)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【회 의 실】</font></td>"; }
    if($me['level'] >= 5) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='기 밀 실' onclick='refreshing(1,4)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【기 밀 실】</font></td>"; }
    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='부대 편성' onclick='refreshing(1,2)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【부대 편성】</font></td>"; }
    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='인 사 부' onclick='refreshing(1,10)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【인 사 부】</font></td>"; }
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='내 무 부' onclick='refreshing(1,13)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【내 무 부】</font></td>"; }
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='사 령 부' onclick='refreshing(1,5)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【사 령 부】</font></td>"; }
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='암 행 부' onclick='refreshing(1,6)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【암 행 부】</font></td>"; }
    $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='토 너 먼 트' onclick='refreshing(1,15)'></td>";
    $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='베 팅 장' onclick='refreshing(1,16)'></td>";
    $result .= "
    </tr>
</table>";

    $result .= "
<table align=center border=0 cellspacing=0 cellpadding=0 style=font-size:13px;word-break:break-all; id=bg2>
    <tr>";

    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='세력 정보' onclick='refreshing(1,7)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【세력 정보】</font></td>"; }
    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='세력 도시' onclick='refreshing(1,8)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【세력 도시】</font></td>"; }
    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='세력 장수' onclick='refreshing(1,9)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【세력 장수】</font></td>"; }
    if($me['level'] >= 1) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='국 법' onclick='refreshing(1,3)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【국 법】</font></td>"; }
    $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='중원 정보' onclick='refreshing(1,14)'></td>";
    $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='현재 도시' onclick='refreshing(1,11)'></td>";
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='감 찰 부' onclick='refreshing(1,18)'></td>"; }
    else {                     $result .= "<td width=111 height=30 align=center><font size=2 color=gray>【감 찰 부】</font></td>"; }
    $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='내 정보 & 설정' onclick='refreshing(1,12)'></td>";
    $result .= "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='거 래 장' onclick='refreshing(1,17)'></td>";
    $result .= "
    </tr>
</table>";

    return $result;
}



function getMapHtml(){
    //NOTE: 필요한가?
    $templates = new League\Plates\Engine('templates');

    return $templates->render('map');
}