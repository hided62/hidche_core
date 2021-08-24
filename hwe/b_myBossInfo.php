<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

increaseRefresh("인사부", 1);
//훼섭 추방을 위해 갱신
TurnExecutionHelper::executeAllCommand();

$me = $db->queryFirstRow('SELECT no,nation,officer_level from general where owner=%i', $userID);
$nationID = $me['nation'];

$meLevel = $me['officer_level'];
if($meLevel == 0) {
    echo "재야입니다.";
    exit();
}

$nation = $db->queryFirstRow('SELECT nation,name,level,color,chief_set from nation where nation=%i', $nationID); //국가정보

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 인사부</title>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/common.js')?>
<?=WebUtil::printJS('../e_lib/select2/select2.full.min.js')?>
<?=WebUtil::printJS('dist_js/bossInfo.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2-bootstrap4.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<script type="text/javascript">
var chiefStatMin = <?=GameConst::$chiefStatMin?>;
var myLevel = <?=$meLevel?>;
</script>

<?php

$ambassadors = $db->query('SELECT no, name, officer_level, penalty, permission FROM general WHERE permission = \'ambassador\' AND nation = %i', $nationID);
$auditors = $db->query('SELECT no, name, officer_level, penalty, permission FROM general WHERE permission = \'auditor\' AND nation = %i', $nationID);
$candidateAmbassadors = [];
$candidateAuditors = [];
foreach($ambassadors as $ambassador){
    $candidateAmbassadors[] = $ambassador;
}
foreach($auditors as $auditor){
    $candidateAuditors[] = $auditor;
}
foreach($db->query('SELECT no, name, nation, officer_level, penalty, permission FROM general WHERE nation = %i AND permission = \'normal\' AND officer_level != 12', $nationID) as $candidate){
    $maxPermission = checkSecretMaxPermission($candidate);
    if($maxPermission == 4){
        $candidateAmbassadors[] = $candidate;
    }
    if($maxPermission >= 3){
        $candidateAuditors[] = $candidate;
    }
}

?>
<script>
var candidateAmbassadors = <?=Json::encode(array_map(function($value){
    return [
        'id'=>$value['no'],
        'text'=>$value['name'],
        "selected"=>($value['permission']!='normal')
    ];
}, $candidateAmbassadors))?>;

var candidateAuditors = <?=Json::encode(array_map(function($value){
    return [
        'id'=>$value['no'],
        'text'=>$value['name'],
        "selected"=>($value['permission']!='normal')
    ];
}, $candidateAuditors))?>;
</script>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>인 사 부<br><?=backButton()?></td></tr>
</table>
<br>

<?php

$lv = getNationChiefLevel($nation['level']);
if($meLevel >= 5) { $btn = "button"; }
else { $btn = "hidden"; }

$level = Util::convertArrayToDict(
    $db->query(
        'SELECT name,officer_level,city,picture,imgsvr,belong from general where nation=%i and officer_level>=%i order by officer_level desc',
        $nationID, $lv
    ),
    'officer_level'
);


$tigers = $db->query('SELECT value, name
    FROM rank_data LEFT JOIN general ON rank_data.general_id = general.no
    WHERE rank_data.nation_id = %i AND rank_data.type = "killnum" AND value > 0 ORDER BY value DESC LIMIT 5',
    $nationID
);// 오호장군
$tigerstr = join(', ', array_map(function($arr){
    $number = number_format($arr['value']);
    return "{$arr['name']}【{$number}】";
}, $tigers));

$eagles = $db->query('SELECT value, name
    FROM rank_data LEFT JOIN general ON rank_data.general_id = general.no
    WHERE rank_data.nation_id = %i AND rank_data.type = "firenum" AND value > 0 ORDER BY value DESC LIMIT 7',
    $nationID
);// 건안칠자
$eaglestr = join(', ', array_map(function($arr){
    $number = number_format($arr['value']);
    return "{$arr['name']}【{$number}】";
}, $eagles));

?>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td align=center style='color:<?=newColor($nation['color'])?>; background-color:<?=$nation['color']?>' colspan=6>
            <font size=5>【 <?=$nation['name']?> 】</font>
        </td>
    </tr>
<?php
for($i=12; $i >= $lv; $i-=2) {
    $i1 = $i;   $i2 = $i - 1;
    $imageTemp1 = GetImageURL($level[$i1]['imgsvr']??0);
    $imageTemp2 = GetImageURL($level[$i2]['imgsvr']??0);
    ?>
    <tr>
        <td width=98 align=center id=bg1><font size=4><?=getOfficerLevelText($i1, $nation['level'])?></font></td>
        <td width=64 class='generalIcon' height=64 style='background:no-repeat center url("<?=$imageTemp1?>/<?=$level[$i1]['picture']??'default.jpg'?>");background-size:64px;'></td>
        <td width=332><font size=4><?=$level[$i1]['name']??'-'?>(<?=$level[$i1]['belong']??'-'?>년)</font></td>
        <td width=98 align=center id=bg1><font size=4><?=getOfficerLevelText($i2, $nation['level'])?></font></td>
        <td width=64 class='generalIcon' height=64 style='background:no-repeat center url("<?=$imageTemp2?>/<?=$level[$i2]['picture']??'default.jpg'?>");background-size:64px;'></td>
        <td width=332><font size=4><?=$level[$i2]['name']??'-'?>(<?=$level[$i2]['belong']??'-'?>년)</font></td>
    </tr>
    <?php
}

?>
<tr>
    <td width=98 align=center id=bg1>오호장군【승전】</td>
    <td colspan=5><?=$tigerstr?></td>
    </tr>
    <tr>
        <td width=98 align=center id=bg1>건안칠자【계략】</td>
        <td colspan=5><?=$eaglestr?></td>
    </tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=6 height=5></td></tr>
    <tr><td colspan=2 align=center bgcolor=red>추 방</td></tr>
    <tr>
        <td width=498 align=right id=bg1>대상 장수</td>
        <td width=498>
<?php

if($meLevel >= 5){
    $candidateStrength = $db->query('SELECT no,name,officer_level,city,npc from general where nation=%i and officer_level!=12 and strength>=%i order by npc,binary(name)', $nationID, GameConst::$chiefStatMin);
    $candidateIntel = $db->query('SELECT no,name,officer_level,city,npc from general where nation=%i and officer_level!=12 and intel>=%i order by npc,binary(name)', $nationID, GameConst::$chiefStatMin);
    $candidateAny = $db->query('SELECT no,name,officer_level,city,npc,leadership,strength,intel,killturn from general where nation=%i and officer_level!=12  order by npc,binary(name)', $nationID); //추방 때문에 조금 더 김
}
else{
    $candidateStrength = [];
    $candidateIntel = [];
    $candidateAny = [];
}


if($meLevel >= 5 && !isOfficerSet($nation['chief_set'], $meLevel)) {
    echo "
            <select id='genlist_kick' size=1 style=color:white;background-color:black;>";

    foreach($candidateAny as $general) {
        if($general['no'] === $me['no']){
            continue;
        }
        echo "
                <option data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} <small>({$general['leadership']}/{$general['strength']}/{$general['intel']}, {$general['killturn']}턴)</small></option>";
    }

    echo "
            </select>
            <input type=$btn id='btn_kick' value=추방>";
}

$officerLevelText = getOfficerLevelText(11, $nation['level']);
echo "
        </td>
    </tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=4 height=5></td></tr>
    <tr><td colspan=4 align=center bgcolor=blue>수 뇌 부 임 명</td></tr>
    <tr>
        <td width=98  align=right id=bg1>".getOfficerLevelText(12, $nation['level'])."</td>
        <td width=398>{$level[12]['name']} 【".CityConst::byID($level[12]['city'])->name."】</td>
        <td width=98  align=right id=bg1>{$officerLevelText}</td>
        <td width=398>
";

if($meLevel >= 5 && !isOfficerSet($nation['chief_set'], 11)) {
    echo "
            <select id='genlist_11' size=1 maxlength=15 style=color:white;background-color:black;>
                <option value=0 data-officer_level='0' data-name=''>____공석____</option>";

    foreach($candidateAny as $general) {
        if($general['officer_level'] == 11) {
            echo "<option style=color:red; selected data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['officer_level'] > 1) {
            echo "<option style=color:orange; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input class='btn_appoint' type=$btn data-officer_level='11' data-officer_level_text='{$officerLevelText}' value=임명>";
} else {
    if(key_exists(11, $level)){
        echo "{$level[11]['name']} 【".CityConst::byID($level[11]['city'])->name."】";
    }

}
echo "
        </td>
    </tr>
";

for($i=10; $i >= $lv; $i--) {
    if($i % 2 == 0) { echo "<tr>"; }
    $officerLevelText = getOfficerLevelText($i, $nation['level']);
    echo "
        <td width=98 align=right id=bg1>{$officerLevelText}</td>
        <td width=398>
    ";


    if($meLevel >= 5 && !isOfficerSet($nation['chief_set'], $i)) {
        echo "
            <select id='genlist_{$i}' size=1 style=color:white;background-color:black;>
                <option value=0 data-officer_level='0' data-name=''>____공석____</option>";

        foreach(($i%2==0?$candidateStrength:$candidateIntel) as $general) {
            if($general['officer_level'] == $i) {
                echo "<option style=color:red; selected data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
            } elseif($general['officer_level'] > 1) {
                echo "<option style=color:orange; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
            } else {
                echo "<option data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
            }
        }

        echo "
            </select>
            <input class='btn_appoint' type=$btn data-officer_level='{$i}' data-officer_level_text='$officerLevelText' value=임명>";
    } else {
        $general = $level[$i]??null;
        if($general){
            echo "{$general['name']} 【".CityConst::byID($general['city'])->name."】";
        }
    }
    echo "</td>";
    if($i % 2 == 1) { echo "</tr>"; }
}
echo "
    <tr><td colspan=4>※ <font color=red>빨간색</font>은 현재 임명중인 장수, <font color=orange>노란색</font>은 다른 관직에 임명된 장수, 하얀색은 일반 장수를 뜻합니다.</td></tr>
</table>

";

if($meLevel == 12):
?>
<table align='center' width='1000' class='tb_layout bg0'>
    <tr><td colspan='4' height='5'></td></tr>
<tr><td colspan='4' align='center' bgcolor='purple'>외 교 권 자 임 명</td></tr>
    <tr>
        <td width=98  align=right id=bg1>외교권자</td>
        <td width=398>
<select id="selectAmbassador" multiple="multiple">
</select>
    <button id='changeAmbassador' type='button'>임명</button>
        </td>
        <td width=98  align=right id=bg1>조언자</td>
        <td width=398>
<select id="selectAuditor" multiple="multiple">
</select>
        <button id='changeAuditor' type='button'>임명</button>
        </td>
    </tr>
</table>
<?php
endif;
?>
<table align=center width=1000 id='officer_list' class='tb_layout bg0'>
    <tr><td colspan=5 height=5></td></tr>
<?php
if($meLevel >= 5) {
    $officerLevelText = getOfficerLevelText(4, $nation['level']);
    echo "
    <tr><td colspan=5 align=center bgcolor=orange>도 시 관 직 임 명</td></tr>
    <tr>
        <td colspan=3 align=right id=bg2>{$officerLevelText} 임명</td>
        <td colspan=2>
            <select id='citylist_4' size=1 style=color:white;background-color:black;>
    ";

    $cityList = $db->query('SELECT city,name,region,officer_set FROM city WHERE nation=%i ORDER BY region,level DESC,binary(name)', $nationID);
    $region = 0;
    foreach($cityList as $city){
        if(isOfficerSet($city['officer_set'], 4)){
            continue;
        }

        if($region != $city['region']) {
            if($region != 0) {
                echo "</optgroup>";
            }
            echo "<optgroup label=' 【 ".CityConst::$regionMap[$city['region']]." 】 ' style=color:skyblue;>";
            $region = $city['region'];
        }

        echo "<option value='{$city['city']}' style=color:white;><span class='name_field'>{$city['name']}</span></option>";
    }
    echo "</optgroup>";

    echo "
            </select>
            <select id='genlist_4' size=1 style=color:white;background-color:black;>
                <option value=0 data-officer_level='0' data-name=''>____공석____</option>
    ";

    foreach($candidateStrength as $general) {
        if($general['officer_level'] == 4) {
            echo "<option style=color:red; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['officer_level'] > 1) {
            echo "<option style=color:orange; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input class='btn_appoint' type=$btn data-officer_level='4' data-officer_level_text='{$officerLevelText}' value=임명>
        </td>
    </tr>";
    $officerLevelText = getOfficerLevelText(3, $nation['level']);
    echo "<tr>
        <td colspan=3 align=right id=bg2>{$officerLevelText} 임명</td>
        <td colspan=2>
            <select id='citylist_3' size=1 style=color:white;background-color:black;>
    ";

    $region = 0;
    foreach($cityList as $city){
        if(isOfficerSet($city['officer_set'], 3)){
            continue;
        }

        if($region != $city['region']) {
            if($region != 0) {
                echo "</optgroup>";
            }
            echo "<optgroup label=' 【 ".CityConst::$regionMap[$city['region']]." 】 ' style=color:skyblue;>";
            $region = $city['region'];
        }

        echo "<option value='{$city['city']}' style=color:white;><span class='name_field'>{$city['name']}</span></option>";
    }
    echo "</optgroup>";

    echo "
            </select>
            <select id='genlist_3' size=1 style=color:white;background-color:black;>
                <option value=0 data-officer_level='0' data-name=''>____공석____</option>
    ";

    foreach($candidateIntel as $general) {
        if($general['officer_level'] == 3) {
            echo "<option style=color:red; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['officer_level'] > 1) {
            echo "<option style=color:orange; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input class='btn_appoint' type=$btn data-officer_level='3'  data-officer_level_text='{$officerLevelText}' value=임명>
        </td>
    </tr>";
    $officerLevelText = getOfficerLevelText(2, $nation['level']);
    echo "<tr>
        <td colspan=3 align=right id=bg2>{$officerLevelText} 임명</td>
        <td colspan=2>
            <select id='citylist_2' size=1 style=color:white;background-color:black;>
    ";

    $region = 0;
    foreach($cityList as $city){
        if(isOfficerSet($city['officer_set'], 2)){
            continue;
        }

        if($region != $city['region']) {
            if($region != 0) {
                echo "</optgroup>";
            }
            echo "<optgroup label=' 【 ".CityConst::$regionMap[$city['region']]." 】 ' style=color:skyblue;>";
            $region = $city['region'];
        }

        echo "<option value='{$city['city']}' style=color:white;><span class='name_field'>{$city['name']}</span></option>";
    }
    echo "</optgroup>";

    echo "
            </select>
            <select id='genlist_2' size=1 style=color:white;background-color:black;>
                <option value=0>____<span class='name_field'>공석</span>____</option>
    ";

    foreach ($candidateAny as $general) {
        if($general['officer_level'] == 2) {
            echo "<option style=color:red; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['officer_level'] > 1) {
            echo "<option style=color:orange; data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option data-officer_level='{$general['officer_level']}' data-name='{$general['name']}' value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input class='btn_appoint' type=$btn data-officer_level='2'  data-officer_level_text='{$officerLevelText}' value=임명>
        </td>
    </tr>
    <tr><td colspan=5>※ <font color=red>빨간색</font>은 현재 임명중인 장수, <font color=orange>노란색</font>은 다른 관직에 임명된 장수, 하얀색은 일반 장수를 뜻합니다.</td></tr>
    ";
}
echo "
    <tr>
        <td width=158 align=center id=bg1 colspan=2><font size=4>도 시</font></td>
        <td width=278 align=center id=bg1><font size=4>태 수 (사관) 【현재도시】</font></td>
        <td width=278 align=center id=bg1><font size=4>군 사 (사관) 【현재도시】</font></td>
        <td width=278 align=center id=bg1><font size=4>종 사 (사관) 【현재도시】</font></td>
    </tr>
";

$citylevel = getCityLevelList();

$officerList = [];

foreach($db->query('SELECT no,name,npc,city,officer_level,officer_city,belong FROM general WHERE nation = %i AND 2 <= officer_level AND officer_level <= 4', $nationID) as $officer){
    $officerCityID = $officer['officer_city'];
    if(!key_exists($officerCityID, $officerList)){
        $officerList[$officerCityID] = [];
    }
    $officerList[$officerCityID][$officer['officer_level']] = $officer;
}

$region = 0;
$dummyOfficer = [
    'name'=>'-',
    'belong'=>0,
];

$textColor = newColor($nation['color']);
$nationColor = $nation['color'];

foreach($db->query('SELECT city,name,level,region,officer_set from city where nation=%i order by region,level desc,binary(name)', $nationID) as $city) {
    $cityID = $city['city'];
    $cityOfficerList = $officerList[$cityID]??[];
?>
<?php if($region != $city['region']): ?>
    <tr><td colspan=5 height=3 id=bg1></td></tr>
    <tr><td colspan=5 id=bg1><font size=4 color=skyblue> 【 <?=CityConst::$regionMap[$city['region']]?> 】 </font></td></tr>
<?php endif; $region = $city['region']; ?>
<tr>
<td width=78 align=center style='color:<?=$textColor?>;background-color:<?=$nationColor?>;font-size:1.2em;'>【<?=$citylevel[$city['level']]?>】</td>
<td width=78 align=right  style='color:<?=$textColor?>;background-color:<?=$nationColor?>;font-size:1.2em;'><?=$city['name']?>&nbsp;&nbsp;</td>

<?php foreach(Util::range(4, 1, -1) as $officerLevel): ?>
<?php     if(key_exists($officerLevel, $cityOfficerList)):
$officer = $cityOfficerList[$officerLevel];
?>
<td style="color:<?=isOfficerSet($city['officer_set'], $officerLevel)?'orange':'white'?>;"><?=$officer['name']?>(<?=$officer['belong']?>년) 【<?=CityConst::byID($officer['city'])->name?>】</td>
<?php     else: ?>
<td>-</td>
<?php     endif; ?>
<?php endforeach; ?>
</tr>
<?php

}
?>
    <tr><td colspan=5>※ <font color=orange>노란색</font>은 변경 불가능, 하얀색은 변경 가능 관직입니다.</td></tr>
</table>
<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
