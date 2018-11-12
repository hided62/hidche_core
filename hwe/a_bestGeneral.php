<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("명장일람", 1);

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1136" />
<title><?=UniqueConst::$serverName?>: 명장일람</title>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/hallOfFame.css')?>

<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
</head>

<body>
<table align=center width=1100 class='tb_layout bg0'>
    <tr><td>명 장 일 람<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1100 class='tb_layout bg0'>
<form name=form1 action=a_bestGeneral.php method=post>
    <tr><td align=center>
        <input type=submit name=btn value='유저 보기'>
        <input type=submit name=btn value='NPC 보기'>
    </td></tr>
</form>
</table>
<div style="margin:auto;width=1000px;">
<?php


$nationName = [0=>'재야'];
$nationColor = [0=>'#000000'];
foreach (getAllNationStaticInfo() as $nation) {
    $nationName[$nation['nation']] = $nation['name'];
    $nationColor[$nation['nation']] = $nation['color'];
}

$types = [
    ["명 성", "int", function($v){$v['value'] = $v['experience']; return $v; }],
    ["계 급", "int", function($v){$v['value'] = $v['dedication']; return $v; }],
    ["계 략 성 공", "int", function($v){
        $v['value'] = $v['firenum'];
        $v['nationName'] = '???';
        $v['pictureFullPath'] = GetImageURL(0)."/default.jpg";
        $v['name'] = '???'; 
        $v['owner_name'] = null;
        $v['bgColor'] = GameConst::$basecolor4;
        $v['fgColor'] = newColor($v['bgColor']);
        return $v;
    }],
    ["전 투 횟 수", "int", function($v){$v['value'] = $v['warnum']; return $v; }],
    ["승 리", "int", function($v){$v['value'] = $v['killnum']; return $v; }],
    ["승 률", "percent", function($v){
        if($v['warnum'] < 10){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['killnum'] / max(1, $v['warnum']);
        }
        return $v;
    }],
    ["사 살", "int", function($v){$v['value'] = $v['killcrew']; return $v; }],
    ["살 상 률", "percent", function($v){
        if($v['warnum'] < 10){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['killcrew'] / max(1, $v['deathcrew']);
        }
        return $v;
    }],
    ["보 병 숙 련 도", "int", function($v){$v['value'] = $v['dex0']; return $v; }],
    ["궁 병 숙 련 도", "int", function($v){$v['value'] = $v['dex10']; return $v; }],
    ["기 병 숙 련 도", "int", function($v){$v['value'] = $v['dex20']; return $v; }],
    ["귀 병 숙 련 도", "int", function($v){$v['value'] = $v['dex30']; return $v; }],
    ["차 병 숙 련 도", "int", function($v){$v['value'] = $v['dex40']; return $v; }],
    ["전 력 전 승 률", "percent", function($v){
        $totalCnt = $v['ttw']+$v['ttd']+$v['ttl'];
        if($totalCnt < 50){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['ttw']/max(1, $totalCnt); 
        }
        return $v; 
    }],
    ["통 솔 전 승 률", "percent", function($v){
        $totalCnt = $v['tlw']+$v['tld']+$v['tll'];
        if($totalCnt < 50){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['tlw']/max(1, $totalCnt);
        }
        return $v; 
    }],
    ["일 기 토 승 률", "percent", function($v){
        $totalCnt = $v['tpw']+$v['tpd']+$v['tpl'];
        if($totalCnt < 50){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['tpw']/max(1, $totalCnt);
        }
        return $v;
    }],
    ["설 전 승 률", "percent", function($v){
        $totalCnt = $v['tiw']+$v['tid']+$v['til'];
        if($totalCnt < 50){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['tiw']/max(1, $totalCnt);
        }
        
        return $v;
    }],
    ["베 팅 투 자 액", "int", function($v){$v['value'] = $v['betgold']; return $v; }],
    ["베 팅 당 첨", "int", function($v){$v['value'] = $v['betwin']; return $v; }],
    ["베 팅 수 익 금", "int", function($v){$v['value'] = $v['betwingold']; return $v; }],
    ["베 팅 수 익 률", "percent", function($v){
        if($v['betgold'] < GameConst::$defaultGold){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['betwingold']/max(1, $v['betgold']);
        }
        return $v;
    }],
];

$generals = array_map(function($general) use($nationColor, $nationName) {
    $general['bgColor'] = $nationColor[$general['nation']]??GameConst::$basecolor4;
    $general['fgColor'] = newColor($general['bgColor']);
    $general['nationName'] = $nationName[$general['nation']];
    
    if(key_exists('picture', $general)){
        $imageTemp = GetImageURL($general['imgsvr']);
        $general['pictureFullPath'] = "$imageTemp/{$general['picture']}";
    }
    else{
        $general['pictureFullPath'] = GetImageURL(0)."/default.jpg";
    }

    return $general;
}, $db->query(
    "SELECT nation,no,name,name2 as owner_name, picture, imgsvr, 
    experience, dedication, firenum, warnum, killnum, killcrew, deathcrew, 
    dex0, dex10, dex20, dex30, dex40, 
    ttw, ttd, ttl, tlw, tld, tll, tpw, tpd, tpl, tiw, tid, til,
    betgold, betwin, betwingold,
    horse, weap, book, item
    FROM general WHERE %l", $btn == "NPC 보기"?"npc>=2":"npc<2"));


$templates = new \League\Plates\Engine('templates');

foreach($types as $idx=>[$typeName, $typeValue, $typeFunc]){
    $validCnt = 0;
    $typeGenerals = array_map(function($general) use($typeValue, $typeFunc, &$validCnt){
        $general = ($typeFunc)($general);
        $value = $general['value'];

        if($value > 0){
            $validCnt+=1;
        }

        if($typeValue == 'percent'){
            $general['printValue'] = number_format($value*100, 2).'%';
        }
        else {
            $general['printValue'] = number_format($value);
        }
        return $general;
    }, $generals);

    usort($typeGenerals, function($lhs, $rhs){
        //내림차순
        return -($lhs['value'] <=> $rhs['value']);
    });


    echo $templates->render('hallOfFrame', [
        'typeName'=>$typeName,
        'generals'=>array_slice($typeGenerals, 0, min(10, $validCnt))
    ]);
}
?>
</div>
<div style="margin:auto;width=1000px;margin-top:5px;">
<?php
//유니크 아이템 소유자
$itemTypes = [
    ["명 마", 'horse', function($v){return getHorseName($v);}, 7, 26, []],
    ["명 검", 'weap', function($v){return getWeapName($v);}, 7, 26, []],
    ["명 서", 'book', function($v){return getBookName($v);}, 7, 26, []],
    ["도 구", 'item', function($v){return displayItemInfo($v);}, 7, 26, []],
];

$simpleItemTypes = array_map(function($itemType){return $itemType[1];}, $itemTypes);

foreach($generals as $general){
    foreach($itemTypes as $itemIdx=>[,$itemType, $itemFunc,,,]){
        $itemCode = $general[$itemType];
        if($itemCode <= 6){
            continue;
        }

        $itemName = ($itemFunc)($itemCode);
        $general['rankName'] = $itemName;
        $general['value'] = $itemCode;
        $itemTypes[$itemIdx][5][$itemCode] = $general;
    }
}

foreach($itemTypes as [$itemNameType, $itemType, $itemFunc, $itemMinCode, $itemMaxCode, $itemOwners]){
    $itemRanker = [];
    for($itemCode = $itemMaxCode; $itemCode >= $itemMinCode; $itemCode--){
        if(!key_exists($itemCode, $itemOwners)){
            $emptyCard = [
                'rankName' => ($itemFunc)($itemCode),
                'pictureFullPath' => GetImageURL(0)."/default.jpg",
                'value'=>$itemCode,
                'name'=>'미발견',
                'bgColor'=>GameConst::$basecolor4,
                'fgColor'=>newColor(GameConst::$basecolor4),
            ];
            $itemRanker[$itemCode] = $emptyCard;
            continue;
        }

        $general = $itemOwners[$itemCode];
        $itemRanker[$itemCode] = $general;
    }

    echo $templates->render('hallOfFrame', [
        'typeName'=>$itemNameType,
        'generals'=>$itemRanker
    ]);
}
?>
</div>
<table align=center width=1100 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

