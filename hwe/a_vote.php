<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("설문조사", 1);

$isVoteAdmin = in_array('vote', $session->acl[DB::prefix()]??[]);
$isVoteAdmin = $isVoteAdmin || $session->userGrade >= 5;

$me = $db->queryFirstRow('SELECT no,vote from general where owner=%i', $userID);

$admin = $gameStor->getValues(['develcost','voteopen','vote_title','vote','votecomment']);
$vote_title = $admin['vote_title']??'-';
$vote = $admin['vote']?:['-'];

?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 설문조사</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/common.js')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('dist_css/common.css')?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
<style>
.little_bar{
    float:left;
    position:relative;
    height:17px;
    line-height:17px;
    text-align:center;
}
</style>
<script type="text/javascript">
function captureKey(e) {
    if(e.keyCode == 13 && e.srcElement.type == 'text') {
        form1.btn.value = '댓글';
        form1.btn.click();
        return false;
    }
}
</script>

</head>
<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>설 문 조 사<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
<form name=form1 action=c_vote.php method=post>
    <tr><td colspan=3 align=center class='bg2'><font size=5>설 문 조 사 (<?=$admin['develcost']*5?>금과 추첨으로 유니크템 증정!)</font></td></tr>
<?php

if ($isVoteAdmin) {
    echo "
    <tr>
        <td width=48  align=center><input type=submit name=btn value='알림'></td>
        <td width=98  align=center><input type=submit name=btn value='수정'></td>
        <td width=848 align=left><input type=text name=title style=width:848px;></td>
    </tr>
    ";
}

$vote_title = Tag2Code($vote_title);
echo "
    <tr>
        <td colspan=2 width=148 align=center class='bg1'>제 목</td>
        <td width=848 align=left>&nbsp;{$vote_title}</td>
    </tr>
";

$voteCount = $db->queryFirstField('SELECT count(no) FROM general WHERE vote>0 AND npc<2');
$allCount = $db->queryFirstField('SELECT count(no) FROM general WHERE npc<2');

$percentage = round($voteCount / $allCount * 100, 1);

$voteTypeCount = count($vote);
for ($i=1; $i < $voteTypeCount; $i++) {
    echo "
    <tr>
        <td width=48 align=center style=color:".getNewColor($i)."; bgcolor=".getVoteColor($i).">{$i}.</td>
        <td width=98 align=center>
    ";
    if ($me['vote'] == 0 && $me['no'] > 0) {
        echo "
            <input type=radio name=sel value={$i}>
        ";
    } elseif ($admin['voteopen'] >= 1 || $isVoteAdmin) {
        $vCount = $db->queryFirstField('SELECT count(no) FROM general WHERE vote=%i', $i);

        $per = @round($vCount / $voteCount * 100, 1);
        echo "{$vCount} 표 ({$per}%)";
    } else {
        echo "추후공개";
    }
    $vote[$i] = Tag2Code($vote[$i]);
    echo "
        </td>
        <td align=left>&nbsp;{$vote[$i]}</td>
    </tr>
    ";
}

echo "
    <tr>
";
if ($me['vote'] == 0 && $me['no'] > 0) {
    echo "
        <td align=center>투표</td>
        <td align=center><input type=submit name=btn value='투표'></td>
    ";
} else {
    echo "
        <td colspan=2 align=center>결산</td>
    ";
}
echo "
        <td align=left>&nbsp;투표율 : {$voteCount} / {$allCount} ({$percentage} %)</td>
    </tr>
";

if ($isVoteAdmin) {
    echo "
    <tr>
        <td align=center><input type=submit name=btn value='리셋'></td>
        <td align=center><input type=submit name=btn value='추가'></td>
        <td align=left><input type=text name=str style=width:848px;></td>
    </tr>
    ";
}

if ($admin['votecomment']) {
    $comment = $admin['votecomment'];
    $commentCount = count($comment);
} else {
    $commentCount = 0;
}
echo "
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td colspan=4 align=center class='bg1'>댓 글</td>
    </tr>
";
for ($i=0; $i < $commentCount; $i++) {
    $cmt = $comment[$i];
    $cmt[2] = Tag2Code($cmt[2]);
    $j = $i+1;
    echo "
    <tr>
        <td width=28  align=center>{$j}.</td>
        <td width=130  align=center>{$cmt[0]}</td>
        <td width=130  align=center>{$cmt[1]}</td>
        <td width=712 align=left>&nbsp;{$cmt[2]}</td>
    </tr>
    ";
}
if ($me['no'] > 0) {
    echo "
    <tr>
        <td width=158 colspan=2 align=center>-</td>
        <td width=130  align=center><input type=submit name=btn value='댓글'></td>
        <td align=left><input type=text name=comment maxlength=60 style=width:700px; onkeydown='return captureKey(event)'></td>
    </tr>
    ";
}
?>
</table>
<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=3 align=center class='bg2'><font size=5>
        전 체 통 계
<?php
if ($isVoteAdmin) {
    echo "
        <input type=submit name=btn value='숨김'>
        <input type=submit name=btn value='전체통계만'>
        <input type=submit name=btn value='전부'>";
}
echo "
    </font></td></tr>";

if ($admin['voteopen'] >= 1 || $isVoteAdmin) {
    echo "
    <tr>
        <td width=130  align=center>전 체</td>
        <td width=128 align=center>{$voteCount} / {$allCount} ({$percentage} %)</td>
        <td width=742>
    ";

    $memCount = max(1, $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2'));

    $totalVote = [];
    $nationVoteCount = [];
    $nationVote = [];

    foreach ($db->query("SELECT nation, vote, count(`no`) as cnt FROM general WHERE npc<2 GROUP BY nation, vote") as $row) {
        $nation = $row['nation'];
        $ownVote = $row['vote'];
        $cnt = $row['cnt'];

        if (!isset($totalVote[$ownVote])) {
            $totalVote[$ownVote] = 0;
        }

        if (!isset($nationVoteCount[$nation])) {
            $nationVoteCount[$nation] = 0;
            $nationVote[$nation] = [];
        }

        if (!isset($nationVote[$nation][$ownVote])) {
            $nationVote[$nation][$ownVote] = 0;
        }

        $totalVote[$ownVote] += $cnt;
        $nationVoteCount[$nation] += $cnt;
        $nationVote[$nation][$ownVote] += $cnt;
    }

    $totalPer = 0;
    for ($i=0; $i < $voteTypeCount; $i++) {
        $per = round(($totalVote[$i]??0) * 100 / $memCount, 1);
        if($i == $voteTypeCount-1){
            $per = 100-$totalPer;
        }
        else{
            $totalPer += $per;
        }


//        if($per < 5) { $vote['cnt'] = "&nbsp;"; }
?>
        <?php if($per == 0): ?>
        <?php elseif($per < 10): ?>
            <div class='little_bar' style='width:<?=$per?>%;color:<?=getNewColor($i)?>;background-color:<?=getVoteColor($i)?>;'></div>
        <?php else:?>
            <div class='little_bar' style='width:<?=$per?>%;color:<?=getNewColor($i)?>;background-color:<?=getVoteColor($i)?>;'><?=$totalVote[$i]?></div>
        <?php endif;?>
<?php
    }

    echo "
        </td>
    </tr>
    ";
}

if ($admin['voteopen'] >= 2 || $isVoteAdmin) {
    $memCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=0 AND npc <2');
    if ($memCount == 0) {
        $memCount = 1;
    }

    if (!isset($nationVoteCount[0])) {
        $nationVoteCount[0] = 0;
    }
    $percentage = round($nationVoteCount[0] / $memCount * 100, 1);

    echo "
    <tr>
        <td align=center bgcolor=black>재 야</td>
        <td align=center>{$nationVoteCount[0]} / {$memCount} ({$percentage} %)</td>
        <td>
    ";

    $totalPer = 0;
    for ($i=0; $i < $voteTypeCount; $i++) {
        $per = round(Util::array_get($nationVote[0][$i], 0) / $memCount * 100, 1);
        if($i == $voteTypeCount-1){
            $per = 100-$totalPer;
        }
        else{
            $totalPer += $per;
        }
//        if($per < 5) { $vote['cnt'] = "&nbsp;"; }
?>
            <?php if($per == 0): ?>
            <?php elseif($per < 10): ?>
                <div class='little_bar' style='width:<?=$per?>%;color:<?=getNewColor($i)?>;background-color:<?=getVoteColor($i)?>;'></div>
            <?php else:?>
                <div class='little_bar' style='width:<?=$per?>%;color:<?=getNewColor($i)?>;background-color:<?=getVoteColor($i)?>;'><?=$nationVote[0][$i]?></div>
            <?php endif;?>
<?php
    }

    echo "
        </td>
    </tr>
    ";

    foreach($db->query('SELECT nation,color,name,gennum from nation order by gennum desc') as $i=>$nation){
        $memCount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc<2', $nation['nation']);



        $voteCount = $nationVoteCount[$nation['nation']] ?? 0;
        if($memCount == 0){
            $percentage = 100;
        }
        else{
            $percentage = round($voteCount / $memCount * 100, 1);
        }


        echo "
    <tr>
        <td align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}>{$nation['name']}</td>
        <td align=center>{$voteCount} / {$memCount} ({$percentage} %)</td>
        <td align=center>
        ";

        $totalPer = 0;
        for ($k=0; $k < $voteTypeCount; $k++) {
            if($memCount == 0){
                $per = 0;
                continue;
            }

            $per = round(($nationVote[$nation['nation']][$k]??0) / $memCount * 100, 1);

            if($i == $voteTypeCount-1){
                $per = 100-$totalPer;
            }
            else{
                $totalPer += $per;
            }

//            if($per < 5) { $vote['cnt'] = "&nbsp;"; }
?>
            <?php if($per == 0): ?>
            <?php elseif($per < 10): ?>
                <div class='little_bar' style='width:<?=$per?>%;color:<?=getNewColor($k)?>;background-color:<?=getVoteColor($k)?>;'></div>
            <?php else:?>
                <div class='little_bar' style='width:<?=$per?>%;color:<?=getNewColor($k)?>;background-color:<?=getVoteColor($k)?>;'><?=$nationVote[$nation['nation']][$k]??0?></div>
            <?php endif;?>
<?php
        }

        echo "
        </td>
    </tr>
        ";
    }
}
?>
</form>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

<?php
function getVoteColor($type)
{
    if ($type > 0) {
        $type = (($type - 1) % 7) + 1;
    }

    switch ($type) {
    default:
    case 0: $color = "black"; break;
    case 1: $color = "red"; break;
    case 2: $color = "orange"; break;
    case 3: $color = "yellow"; break;
    case 4: $color = "green"; break;
    case 5: $color = "blue"; break;
    case 6: $color = "navy"; break;
    case 7: $color = "purple"; break;
    }

    return $color;
}

function getNewColor($type)
{
    if ($type > 0) {
        $type = (($type - 1) % 7) + 1;
    }

    switch ($type) {
    case 2: $color = "black"; break;
    case 3: $color = "black"; break;
    default:$color = "white"; break;
    }

    return $color;
}
