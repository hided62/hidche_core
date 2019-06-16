<?php
namespace sammo;

function printLimitMsg($turntime) {
    //FIXME: template로 이동.
?>
<!DOCTYPE html>
<html>
<head>
<title>접속제한</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
<font size=4><b>
접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다. (다음 접속 가능 시각 : <?=$turntime?>)<br>
(자신의 턴이 되면 다시 접속 가능합니다. 당신의 건강을 위해 잠시 쉬어보시는 것은 어떨까요? ^^)<br>
</b></font>
</body>
</html>
<?php
}


function bar($per, $h=7) {
    if($h == 7) { $bd = 0; $h =  7; $h2 =  5; }
    else        { $bd = 1; $h = 12; $h2 =  8; }

    $per = round($per, 1);
    
    $str = "<div class='bar_out' style='height:{$h}px;'>
    <div class='bar_in' style='background:url(".ServConfig::$gameImagePath."/pr{$h2}.gif)'></div>
    <div style='width:{$per}%;background:url(".ServConfig::$gameImagePath."/pb{$h2}.gif)'></div>
    </div>";
    return $str;
}


function optionsForCities() {
    return join('', array_map(function($city){
        return "<option value='{$city->id}'>{$city->name}</option>";
    }, CityConst::all()));
}

function Submit($url, $msg="", $msg2="") {
    echo "a";   // 파폭 버그 때문
    echo "
<form method=post name=f1 action='{$url}'>
    <input type=hidden name=msg value=\"{$msg}\">
    <input type=hidden name=msg2 value=\"{$msg2}\">
</form>
<script>f1.submit();</script>
    ";
}


function GetNationColors() {
    $colors = array("#FF0000", "#800000", "#A0522D", "#FF6347", "#FFA500", "#FFDAB9", "#FFD700", "#FFFF00",
        "#7CFC00", "#00FF00", "#808000", "#008000", "#2E8B57", "#008080", "#20B2AA", "#6495ED", "#7FFFD4",
        "#AFEEEE", "#87CEEB", "#00FFFF", "#00BFFF", "#0000FF", "#000080", "#483D8B", "#7B68EE", "#BA55D3",
        "#800080", "#FF00FF", "#FFC0CB", "#F5F5DC", "#E0FFFF", "#FFFFFF", "#A9A9A9");
    return $colors;
}


function backButton() {
    return "
<input type=button value='돌아가기' onclick=location.replace('./')><br>
";
}

function CoreBackButton() {
    return "
<input type=button value='돌아가기' onclick=location.replace('b_chiefcenter.php')><br>
";
}

function closeButton() {
    return "
<input type=button value='창 닫기' onclick=window.close()><br>
";
}


function printCitiesBasedOnDistance(int $cityNo, int $maxDistance=1):string {
    $distanceList = searchDistance($cityNo, $maxDistance, true);
    $result = [];

    for($dist = 1; $dist <= $maxDistance; $dist++){
        $cityList = array_map(function($cityID){
            return CityConst::byID($cityID)->name;
        }, Util::array_get($distanceList[$dist], []));

        $cityStr = join(', ', $cityList);

        switch($dist) {
            case 1: $color = "magenta"; break;
            case 2: $color = "orange"; break;
            default: $color = "yellow"; break;
        }

        $result[] = "{$dist}칸 떨어진 도시 : <span style='color:{$color};font-weight:bold;'>{$cityStr}</span>";
    }
    return join("<br>\n", $result);
}


function info($type=0) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year', 'month', 'turnterm', 'maxgeneral']);

    $termtype = "{$admin['turnterm']}분 턴";

    $gencount = $db->queryFirstField('SELECT count(no) FROM general WHERE npc < 2');
    $npccount = $db->queryFirstField('SELECT count(no) FROM general WHERE npc >= 2');

    switch($type) {
    case 0:
        return "현재 : {$admin['year']}年 {$admin['month']}月 (<font color=cyan>$termtype</font> 서버)<br> 등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color=cyan>NPC {$npccount} 명</font>";
    case 1:
        return "현재 : {$admin['year']}年 {$admin['month']}月 (<font color=cyan>$termtype</font> 서버)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color=cyan>NPC {$npccount} 명</font>";
    case 2:
        return "현재 : {$admin['year']}年 {$admin['month']}月 (<font color=cyan>$termtype</font> 서버)";
    case 3:
        return "등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color=cyan>NPC {$npccount} 명</font>";
    }
}

