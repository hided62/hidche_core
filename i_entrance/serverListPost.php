<?php
// 외부 파라미터

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.DBS.PHP);
require_once(ROOT.W.F_CONFIG.W.SETTINGS.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

$rs = $DB->Select('ID, GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

$response['serverCount'] = $_serverCount;
$response['servers'] = '';
for($i=0; $i < $_serverCount; $i++) {
    if(!$SETTINGS[$i]->IsExist()) {
        $response['servers'] .= "
<div class='Entrance_ServerList'>
    <div class='Entrance_ServerListServer'><br>{$_serverNames[$i]}</div>
    <div class='Entrance_ServerListDown'><br>- 폐 쇄 중 -</div>
</div>
";
//<br>(참여금이 모여 운영비가 마련될수록 많은 서버가 열립니다!)
    } else {
        $rs = $DBS[$i]->Select('ISUNITED, NPCMODE, YEAR, MONTH, SCENARIO, MAXGENERAL, TURNTERM', 'game', "NO='1'");
        $game = $DBS[$i]->Get($rs);

        $rs = $DBS[$i]->Select('COUNT(*) AS CNT', 'nation', "LEVEL>'0'");
        $nation = $DBS[$i]->Get($rs);

        $rs = $DBS[$i]->Select('COUNT(*) AS CNT', 'general', "NPC<'2'");
        $gen = $DBS[$i]->Get($rs);

        $rs = $DBS[$i]->Select('COUNT(*) AS CNT', 'general', "NPC>='2'");
        $npc = $DBS[$i]->Get($rs);

        unset($me);
        $rs = $DBS[$i]->Select('NAME, PICTURE, IMGSVR', 'general', "USER_ID='{$member['ID']}'");
        $me = $DBS[$i]->Get($rs);

        if($game['ISUNITED'] == 2) { $state = "§천하통일§"; }
        else { $state = "&lt;{$nation['CNT']}국 경쟁중&gt;"; }
        $state = "<font size=2>{$state}</font>";

        if($me)                       { $site = "login_process.php"; }
        elseif($game['NPCMODE'] == 1) { $site = "selection.php"; }
        else                          { $site = "join.php"; }

        $info = "서기 {$game['YEAR']}년 {$game['MONTH']}월 (<font color=orange>".getScenario($game['SCENARIO'])."</font>)<br>
                유저 : {$gen['CNT']} / {$game['MAXGENERAL']}명 <font color=cyan>NPC : {$npc['CNT']}명</font> (<font color=limegreen>".getTurnTerm($game['TURNTERM'])."</font>)";

        if($member['GRADE'] == 0) {
            $character = "<div class='Entrance_ServerListBlock'>- 계정 블럭 -</div>";
        } elseif($_serverLevels[$i] == 2 && $member['GRADE'] < 2) {
            $character = "<div class='Entrance_ServerListBlock'>- 클로즈 테스트중 -</div>";
        } elseif($_serverLevels[$i] == 3 && $member['GRADE'] < 3) {
            $character = "<div class='Entrance_ServerListBlock'>- 클로즈 테스트중 -</div>";
        } elseif($_serverLevels[$i] == 4 && $member['GRADE'] < 4) {
            $character = "<div class='Entrance_ServerListBlock'>- 클로즈 테스트중 -</div>";
        } elseif($_serverLevels[$i] == 5 && $member['GRADE'] < 5) {
            $character = "<div class='Entrance_ServerListBlock'>- 부운영자 테스트중 -</div>";
        } elseif($_serverLevels[$i] >= 6 && $member['GRADE'] < 6) {
            $character = "<div class='Entrance_ServerListBlock'>- 운영자 테스트중 -</div>";
        } elseif($me) {
            if($me['PICTURE'] == '') {
                $picture = '<img src="'.IMAGE.W.'default.jpg">';
            } else {
                if($me['IMGSVR'] == 0) {
                    $picture = '<img src="'.IMAGE.W."{$me['PICTURE']}\">";
                } else {
                    $picture = '<img src="../d_pic/'.W."{$me['PICTURE']}\">";
                }
            }

            $character = "
<div class='Entrance_ServerListCharacter'>{$picture}</div>
<div class='Entrance_ServerListName'>{$me['NAME']}</div>
<input class='Entrance_ServerListLogin' type='button' value='입장' onclick='Entrance_Enter(\"{$_serverDirs[$i]}/login_process.php\")'>
";
        } elseif($gen['CNT'] >= $game['MAXGENERAL']) {
            $character = "<div class='Entrance_ServerListBlock'>- 장수 등록 마감 -</div>";
        } else {
            $character = "<div class='Entrance_ServerListNoRegister'>- 미 등 록 -</div>";
            if($game['NPCMODE'] == 1) {
                $character .= "<input class='Entrance_ServerListSelect' type='button' value='장수선택' onclick='Entrance_Enter(\"{$_serverDirs[$i]}/select_npc.php\")'>";
                $character .= "<input class='Entrance_ServerListSelect' type='button' value='장수생성' onclick='Entrance_Enter(\"{$_serverDirs[$i]}/join.php\")'>";
            } else {
                $character .= "<input class='Entrance_ServerListLogin' type='button' value='장수생성' onclick='Entrance_Enter(\"{$_serverDirs[$i]}/join.php\")'>";
            }
        }

        $response['servers'] .= "
<div class='Entrance_ServerList'>
    <div class='Entrance_ServerListServer'><br>{$_serverNames[$i]}<br>{$state}</div>
    <div class='Entrance_ServerListInfo'><br>{$info}</div>
    {$character}
</div>
";
    }
}

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);

function getScenario($scenario) {
    switch($scenario) {
    case  0: $str = "공백지모드"; break;
    case  1: $str = "역사모드1 : 184년 황건적의 난"; break;
    case  2: $str = "역사모드2 : 190년 반동탁연합"; break;
    case  3: $str = "역사모드3 : 194년 군웅할거"; break;
    case  4: $str = "역사모드4 : 196년 황제는 허도로"; break;
    case  5: $str = "역사모드5 : 200년 관도대전"; break;
    case  6: $str = "역사모드6 : 202년 원가의 분열"; break;
    case  7: $str = "역사모드7 : 207년 적벽대전"; break;
    case  8: $str = "역사모드8 : 213년 익주 공방전"; break;
    case  9: $str = "역사모드9 : 219년 삼국정립"; break;
    case 10: $str = "역사모드10 : 225년 칠종칠금"; break;
    case 11: $str = "역사모드11 : 228년 출사표"; break;

    case 12: $str = "IF모드1 : 191년 백마장군의 위세"; break;

    case 20: $str = "가상모드1 : 180년 영웅 난무"; break;
    case 21: $str = "가상모드1 : 180년 영웅 집결"; break;
    case 22: $str = "가상모드2 : 179년 훼신 집결"; break;
    case 23: $str = "가상모드3 : 180년 영웅 시대"; break;
    case 24: $str = "가상모드4 : 180년 결사항전"; break;
    case 25: $str = "가상모드5 : 180년 영웅독존"; break;
    case 26: $str = "가상모드6 : 180년 무풍지대"; break;
    case 27: $str = "가상모드7 : 180년 가요대잔치"; break;
    case 28: $str = "가상모드8 : 180년 확산성 밀리언 아서"; break;
    default: $str = "시나리오?"; break;
    }
    return $str;
}

function getTurnTerm($term) {
    switch($term) {
    case 0: $str = "120분 턴 서버"; break;
    case 1: $str = "60분 턴 서버"; break;
    case 2: $str = "30분 턴 서버"; break;
    case 3: $str = "20분 턴 서버"; break;
    case 4: $str = "10분 턴 서버"; break;
    case 5: $str = "5분 턴 서버"; break;
    case 6: $str = "2분 턴 서버"; break;
    case 7: $str = "1분 턴 서버"; break;
    }
    return $str;
}

?>

