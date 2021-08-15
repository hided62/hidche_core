<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

increaseRefresh("부대편성", 1);

$me = $db->queryFirstRow('SELECT no,nation,troop FROM general WHERE owner=%i', $userID);

$troops = [];
foreach($db->query('SELECT troop_leader,name FROM troop WHERE nation = %i', $me['nation']) as $rawTroop){
    $troops[$rawTroop['troop_leader']] = [
        'name'=>$rawTroop['name'],
        'users'=>[]
    ];
}

foreach($db->query(
    'SELECT no,name,turntime,troop,city FROM general WHERE troop!=0 AND nation = %i ORDER BY turntime ASC',
    $me['nation']
) as $general
){
    if(!key_exists($general['troop'], $troops)){
        trigger_error("올바르지 않은 부대 소속 {$general['no']}, {$general['name']} : {$general['troop']}");
        continue;
    }

    $general['cityText'] = CityConst::byID($general['city'])->name;

    $troops[$general['troop']]['users'][] = $general;
}

if($troops){
    $troopLeaders = $db->query(
        'SELECT no,name,picture,imgsvr,turntime,city,troop FROM general WHERE no IN %li',
        array_keys($troops)
    );
    $generalTurnList = [];

    foreach($db->queryAllLists(
        'SELECT general_id, turn_idx, brief FROM general_turn WHERE general_id IN %li AND turn_idx < 5 ORDER BY general_id ASC, turn_idx ASC', 
        array_column($troopLeaders, 'no')
        ) as [$generalID, $turnIdx, $brief]
    ){
        if(!key_exists($generalID, $generalTurnList)){
            $generalTurnList[$generalID] = [];
        }
        $generalTurnList[$generalID][$turnIdx] = $brief;
    }
    
    foreach($troopLeaders as $troopLeader){
        $imageTemp = GetImageURL($troopLeader['imgsvr']);
        
        $troopLeader['pictureFullPath'] = "$imageTemp/{$troopLeader['picture']}";
        $troopLeader['cityText'] = CityConst::byID($troopLeader['city'])->name;

        $turnText = [];
        foreach($generalTurnList[$troopLeader['no']] as $rawTurnIdx => $brief){
            if($brief != '집합'){
                $brief = '~';
            }
            $turnIdx = $rawTurnIdx + 1;
            $turnText[] = "{$turnIdx} : {$brief}";
        }
        $troopLeader['turnText'] = join('<br>', $turnText);
        $troops[$troopLeader['troop']]['leader'] = $troopLeader;
    }
}

uasort($troops, function($lhs, $rhs){
    return $lhs['leader']['turntime']<=>$rhs['leader']['turntime'];
})

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 부대편성</title>
<?=WebUtil::preloadJS('js/troop.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/vendors.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/troop.css')?>
<script>

</script>
</head>

<body>
<div style="width:1000px;margin:auto;">
<table width=1000 class='tb_layout bg0'>
    <tr><td>부 대 편 성<br><?=backButton()?></td></tr>
</table>
<table id="troop_list" class='tb_layout bg0'>
    <thead>
    <tr>
        <td width=64  class='bg1 center'>선 택</td>
        <td width=130  class='bg1 center'>부 대 정 보</td>
        <td width=100  class='bg1 center'>부 대 장</td>
        <td width=576 class='bg1 center' style=table-layout:fixed;word-break:break-all;>장 수</td>
        <td width=130  class='bg1 center' style=table-layout:fixed;word-break:break-all;>부대장행동</td>
    </tr>
    </thead>
    <tfoot><tr><td colspan='5'>
    <?php if(!$troops): ?>
    <?php elseif($me['troop'] == 0): ?>
        <input type=button id='btnJoinTroop' value='부 대 가 입'>
    <?php else: ?>
        <input type=button id="btnLeaveTroop" value='부 대 탈 퇴'>
    <?php endif;?>
    </td></tr></tfoot>
    <tbody>
<?php
foreach ($troops as $troopNo=>$troop) {
    $troopLeader = $troop['leader'];
    $genlistText = [];
    $cityText = $troopLeader['cityText'];
    $cityID = $troopLeader['city'];
    $leaderID = $troopLeader['no'];

    foreach ($troop['users'] as $troopUser) {
        $spanClass = 'troopUser';
        if ($troopUser['city'] !== $cityID) {
            $spanClass.= ' diffCity';
        }
        if ($troopUser['no'] == $leaderID) {
            $spanClass.= ' leader';
        }
        $genlistText[] = "<span class='$spanClass' data-general-id='{$troopUser['no']}'
            ><span class='generalName'>{$troopUser['name']}</span><span class='cityText'>【{$troopUser['cityText']}】</span
            ></span>";
    }

    $genlistText = sprintf('%s (%d명)', join(', ', $genlistText), count($genlistText)); ?>

<?php if ($me['troop'] == 0): ?>
    <tr>
        <td align=center rowspan=2><input type='radio' class='troopId' name='troop' value='<?=$troopNo?>'></td>
        <td align=center><?=$troop['name']?><br>【 <?=$cityText?> 】</td>
        <td height=64 class='generalIcon' style='background:no-repeat center url("<?=$troopLeader['pictureFullPath']?>");background-size:64px;'>&nbsp;</td>
        <td rowspan=2 width=62><?=$genlistText?></td>
        <td rowspan=2><?=$troopLeader['turnText']?></td>
    </tr>
    <tr>
        <td align=center><font size=2>【턴】 <?=substr($troopLeader['turntime'], 14, 5)?></font></td>
        <td align=center><font size=1><?=$troopLeader['name']?></font></td></tr>
    <tr><td colspan=5>

<?php else: ?>
    <tr>
        <td align=center rowspan=2>&nbsp;</td>
        <td align=center ><?=$troop['name']?><br>【 <?=$cityText?> 】</td>
        <td height=64 class='generalIcon' style='background:no-repeat center url("<?=$troopLeader['pictureFullPath']?>");background-size:64px;'>&nbsp;</td>
        <td rowspan=2 width=62><?=$genlistText?></td>
        <td rowspan=2>
        <?php if ($me['no'] == $troopLeader['no']): ?>
            <select id='genNo' name=gen size=3 style=color:white;background-color:black;font-size:13px;width:128px;>";
                <?php foreach ($troop['users'] as $troopUser): ?>
                    <?php if ($troopUser['no'] == $me['no']) {
        continue;
    } ?>
                    <option value='<?=$troopUser['no']?>'><?=$troopUser['name']?></option>
                <?php endforeach; ?>
            </select><br>
            <input type=button id='btnKickTroop' value='부 대 추 방' style=width:130px;height:25px;>
        <?php else: ?>
            <?=$troopLeader['turnText']?>
        <?php endif; ?>
        </td>
    </tr>
    <tr><td align=center><font size=2>【턴】 <?=substr($troopLeader['turntime'], 14, 5)?></font></td>
    <td align=center><font size=1><?=$troopLeader['name']?></font></td></tr>
    <tr><td colspan=5></td></tr>
<?php endif;

} //foreach ($troops as $troopNo=>$troop) {
?>

</tbody>

</table>
<br>
<table width=1000 class='tb_layout bg0'>
    <tr>
        <td width=80 id=bg1>부 대 명</td>
        <td width=130><input type=text style=color:white;background-color:black; size=18 maxlength=18 id='nameplate'></td>
    <?php if($me['troop'] == 0): ?>
        <td><input type=button id='btnCreateTroop' value='부 대 창 설'></td>
    <?php else: ?>
        <td><input type=button id='btnChangeTroopName' value='부 대 변 경'></td>
    <?php endif; ?>
    </tr>
</table>

<table width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</div>
<?=WebUtil::printJS('js/troop.js')?>
</body>
</html>

