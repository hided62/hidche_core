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
$templates = new \League\Plates\Engine(__DIR__.'/templates');

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1136" />
<title><?=UniqueConst::$serverName?>: 명장일람</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('dist_css/common.css')?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
<?=WebUtil::printCSS('css/hallOfFame.css')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/common.js')?>
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
<div style="margin:auto;width:1100px;">
<?php

$ownerNameList = [];
if($gameStor->isunited){
    foreach(RootDB::db()->queryAllLists('SELECT no, name FROM member') as [$ownerID, $ownerName]){
        $ownerNameList[$ownerID] = $ownerName;
    }
}

$nationName = [0=>'재야'];
$nationColor = [0=>'#000000'];
foreach (getAllNationStaticInfo() as $nation) {
    $nationName[$nation['nation']] = $nation['name'];
    $nationColor[$nation['nation']] = $nation['color'];
}

$types = [
    ["명 성", "int", function($v){$v['value'] = $v['experience']; return $v; }],
    ["계 급", "int", function($v){$v['value'] = $v['dedication']; return $v; }],
    ["계 략 성 공", "int", function($v) use ($gameStor){
        $v['value'] = $v['firenum'];
        if($gameStor->isunited){
            return $v;
        }
        $v['nationName'] = '???';
        $v['pictureFullPath'] = GetImageURL(0)."/default.jpg";
        $v['name'] = '???';
        $v['ownerName'] = null;
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
    ["점 령", "int", function($v){$v['value'] = $v['occupied']; return $v; }],
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
    ["대 인 사 살", "int", function($v){$v['value'] = $v['killcrew_person']; return $v; }],
    ["대 인 살 상 률", "percent", function($v){
        if($v['warnum'] < 10){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['killcrew_person'] / max(1, $v['deathcrew_person']);
        }
        return $v;
    }],
    ["보 병 숙 련 도", "int", function($v){$v['value'] = $v['dex1']; return $v; }],
    ["궁 병 숙 련 도", "int", function($v){$v['value'] = $v['dex2']; return $v; }],
    ["기 병 숙 련 도", "int", function($v){$v['value'] = $v['dex3']; return $v; }],
    ["귀 병 숙 련 도", "int", function($v){$v['value'] = $v['dex4']; return $v; }],
    ["차 병 숙 련 도", "int", function($v){$v['value'] = $v['dex5']; return $v; }],
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
        $totalCnt = $v['tsw']+$v['tsd']+$v['tsl'];
        if($totalCnt < 50){
            $v['value'] = 0;
        }
        else{
            $v['value'] = $v['tsw']/max(1, $totalCnt);
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

$generals = [];
foreach($db->query(
    "SELECT nation,no,name,owner_name as ownerName, owner, picture, imgsvr,
    experience, dedication,
    dex1, dex2, dex3, dex4, dex5,
    horse, weapon, book, item
    FROM general WHERE %l", $btn == "NPC 보기"?"npc>=2":"npc<2") as $general
){
    $generalID = $general['no'];
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

    $generals[$generalID] = $general;
}

foreach($db->queryAllLists('SELECT general_id, `type`, `value` FROM rank_data') as [$generalID, $typeKey, $value]){
    if(!key_exists($generalID, $generals)){
        continue;
    }
     $generals[$generalID][$typeKey] = $value;
}

$templates = new \League\Plates\Engine('templates');

foreach($types as $idx=>[$typeName, $typeValue, $typeFunc]){
    $validCnt = 0;
    $typeGenerals = array_map(function($general) use($typeValue, $typeFunc, &$validCnt, $ownerNameList){
        $general['ownerName'] = $ownerNameList[$general['owner']]??null;
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
<div style="margin:auto;width:1100px;margin-top:5px;">
<?php
//유니크 아이템 소유자
$itemTypes = [
    'horse'=>'명 마',
    'weapon'=>'명 검',
    'book'=>'명 서',
    'item'=>'도 구'
];

$simpleItemTypes = array_keys($itemTypes); array_map(function($itemType){return $itemType[1];}, $itemTypes);
$itemOwners = [];

foreach($generals as $general){
    foreach($itemTypes as $itemType=>$itemTypeName){
        $itemClassName = $general[$itemType];
        $itemClass = buildItemClass($itemClassName);
        if($itemClass->isBuyable()){
            continue;
        }

        if(key_exists($itemClassName, $itemOwners)){
            $itemOwners[$itemClassName][] = $general;
        }
        else{
            $itemOwners[$itemClassName] = [$general];
        }

    }
}

foreach(GameConst::$allItems as $itemType=>$itemList){
    $itemNameType = $itemTypes[$itemType];
    $itemList = array_reverse($itemList, true);


    $itemRanker = [];
    foreach($itemList as $itemClassName=>$itemCnt){
        if($itemCnt==0){
            continue;
        }
        $itemClass = buildItemClass($itemClassName);

        if($itemClass->isBuyable()){
            continue;
        }

        $info = $itemClass->getInfo();
        $name = $itemClass->getName();


        if($info){
            $name = $templates->render('tooltip', [
                'text'=>$name,
                'info'=>$info,
            ]);
        }
        foreach(Util::range($itemCnt) as $itemIdx){
            if(($itemOwners[$itemClassName][$itemIdx]??null) === null){
                $emptyCard = [
                    'rankName' => $name,
                    'pictureFullPath' => GetImageURL(0)."/default.jpg",
                    'value'=>$itemClassName,
                    'name'=>'미발견',
                    'bgColor'=>GameConst::$basecolor4,
                    'fgColor'=>newColor(GameConst::$basecolor4),
                ];
                $itemRanker[] = $emptyCard;
                continue;
            }

            $general = $itemOwners[$itemClassName][$itemIdx];

            $card = [
                'rankName' => $name,
                'pictureFullPath' => $general['pictureFullPath'],
                'value'=>$itemClassName,
                'nationName'=>$general['nationName'],
                'name'=>$general['name'],
                'bgColor'=>$general['bgColor'],
                'fgColor'=>$general['fgColor'],
            ];
            $itemRanker[] = $card;
        }
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
