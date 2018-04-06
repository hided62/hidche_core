<?php
namespace sammo;

function printLimitMsg($turntime) {
    //FIXME: template로 이동.
?>
<!DOCTYPE html>
<html>
<head>
<title>접속제한</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
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
    global $images;
    if($h == 7) { $bd = 0; $h =  7; $h2 =  5; }
    else        { $bd = 1; $h = 12; $h2 =  8; }

    $per = round($per, 1);
    if($per < 1 || $per > 99) { $per = round($per); }
    $str1 = "<td width={$per}% background={$images}/pb{$h2}.gif></td>";
    $str2 = "<td width=*% background={$images}/pr{$h2}.gif></td>";
    if($per <= 0) { $str1 = ""; }
    elseif($per >= 100) { $str2 = ""; }
    $str = "
    <table width=100% height={$h} border={$bd} cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:1;>
        <tr>{$str1}{$str2}</tr>
    </table>";
    return $str;
}


function OptionsForCitys() {
    foreach(CityConst::all() as $city){
        echo "
        <option value={$city->id}>{$city->name}</option>";
    }
}

function Submit($url, $msg="", $msg2="") {
    echo "a";   // 파폭 버그 때문
    echo "
<form method=post name=f1 action='{$url}'>
    <input type=hidden name=msg value='{$msg}'>
    <input type=hidden name=msg2 value='{$msg2}'>
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
<input type=button value='돌아가기' onclick=location.replace('index.php')><br>
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


function printCitysName(int $cityNo, int $maxDistance=1) {
    $distanceList = searchDistance($cityNo, $maxDistance, true);

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

        echo "{$dist}칸 떨어진 도시 : <span style='color:{$color};font-weight:bold;'>{$cityStr}</span><br>";
    }
}


function info($type=0) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select year,month,turnterm,maxgeneral from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $termtype = "{$admin['turnterm']}분 턴";

    $query = "select no from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    $query = "select no from general where npc>=2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $npccount = MYDB_num_rows($result);

    switch($type) {
    case 0:
        echo "현재 : {$admin['year']}年 {$admin['month']}月 (<font color=cyan>$termtype</font> 서버)<br> 등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color=cyan>NPC {$npccount} 명</font>";
        break;
    case 1:
        echo "현재 : {$admin['year']}年 {$admin['month']}月 (<font color=cyan>$termtype</font> 서버)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color=cyan>NPC {$npccount} 명</font>";
        break;
    case 2:
        echo "현재 : {$admin['year']}年 {$admin['month']}月 (<font color=cyan>$termtype</font> 서버)";
        break;
    case 3:
        echo "등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color=cyan>NPC {$npccount} 명</font>";
        break;
    }
}

