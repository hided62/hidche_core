<?php
include "lib.php";
include "func.php";
$connect = dbConn();
increaseRefresh($connect, "설문조사", 1);

$query = "select no,userlevel,vote from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select develcost,voteopen,vote,votecomment from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$vote = explode("|", $admin['vote']);
if($vote[0] == "") {
    $vote[0] = "-";
}

?>
<html>
<head>
<title>설문조사</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
<script type="text/javascript">
function captureKey(e) {
    if(e.keyCode == 13 && e.srcElement.type == 'text') {
        form1.btn.value = '댓글';
        form1.btn.click();
        return false;
    }
}
</script>
<?php require('analytics.php'); ?>
</head>
<body oncontextmenu='return false'>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>설 문 조 사<br><?php closeButton(); ?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
<form name=form1 action=c_vote.php method=post>
    <tr><td colspan=3 align=center id=bg2><font size=5>설 문 조 사 (<?=$admin['develcost']*5;?>금과 추첨으로 유니크템 증정!)</font></td></tr>
<?php

if($me['userlevel'] >= 5) {
    echo "
    <tr>
        <td width=48  align=center><input type=submit name=btn value='알림'></td>
        <td width=98  align=center><input type=submit name=btn value='수정'></td>
        <td width=848 align=left><input type=text name=title style=width:848;></td>
    </tr>
    ";
}

$vote[0] = Tag2Code($vote[0]);
echo "
    <tr>
        <td colspan=2 width=148 align=center id=bg1>제 목</td>
        <td width=848 align=left>&nbsp;{$vote[0]}</td>
    </tr>
";

$query = "select no from general where vote>0 and npc<2";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$voteCount = MYDB_num_rows($result);

$query = "select no from general where npc<2";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$allCount = MYDB_num_rows($result);

$percentage = round($voteCount / $allCount * 100, 1);

$voteTypeCount = count($vote);
for($i=1; $i < $voteTypeCount; $i++) {
    echo "
    <tr>
        <td width=48 align=center style=color:".getNewColor($i)."; bgcolor=".getColor($i).">{$i}.</td>
        <td width=98 align=center>
    ";
    if($me['vote'] == 0 && $me['no'] > 0) {
        echo "
            <input type=radio name=sel value={$i}>
        ";
    } elseif($admin['voteopen'] >= 1 || $me['userlevel'] >= 5) {
        $query = "select no from general where vote='{$i}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $vCount = MYDB_num_rows($result);

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
if($me['vote'] == 0 && $me['no'] > 0) {
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

if($me['userlevel'] >= 5) {
    echo "
    <tr>
        <td align=center><input type=submit name=btn value='리셋'></td>
        <td align=center><input type=submit name=btn value='추가'></td>
        <td align=left><input type=text name=str style=width:848;></td>
    </tr>
    ";
}

if($admin['votecomment'] != "") {
    $comment = explode("|", $admin['votecomment']);
    $commentCount = count($comment);
} else {
    $commentCount = 0;
}
echo "
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td colspan=4 align=center id=bg1>댓 글</td>
    </tr>
";
for($i=0; $i < $commentCount; $i++) {
    $cmt = explode(":", $comment[$i]);
    $cmt[2] = Tag2Code($cmt[2]);
    $j = $i+1;
    echo "
    <tr>
        <td width=28  align=center>{$j}.</td>
        <td width=83  align=center>{$cmt[0]}</td>
        <td width=83  align=center>{$cmt[1]}</td>
        <td width=788 align=left>&nbsp;{$cmt[2]}</td>
    </tr>
    ";
}
if($me['no'] > 0) {
    echo "
    <tr>
        <td width=108 colspan=2 align=center>-</td>
        <td width=83  align=center><input type=submit name=btn value='댓글'></td>
        <td align=left><input type=text name=comment maxlength=60 style=width:798; onkeydown='return captureKey(event)'></td>
    </tr>
    ";
}
?>
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td colspan=3 align=center id=bg2><font size=5>
        전 체 통 계
<?php
if($me['userlevel'] >= 5) {
    echo "
        <input type=submit name=btn value='숨김'>
        <input type=submit name=btn value='전체통계만'>
        <input type=submit name=btn value='전부'>";
}
echo "
    </font></td></tr>";

if($admin['voteopen'] >= 1 || $me['userlevel'] >= 5) {
    echo "
    <tr>
        <td width=98  align=center>전 체</td>
        <td width=128 align=center>{$voteCount} / {$allCount} ({$percentage} %)</td>
        <td width=768 align=center>
            <table align=center width=100% height=100% border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                <tr>
    ";

    $query = "select no from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $memCount = MYDB_num_rows($result);

    $query = "select nation,vote from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $vote = MYDB_fetch_array($result);

        $totalVote[$vote['vote']]++;
        if($vote['vote'] > 0) { $nationVoteCount[$vote['nation']]++; }
        $nationVote[$vote['nation']][$vote['vote']]++;
    }

    for($i=0; $i < $voteTypeCount; $i++) {
        $per = @round($totalVote[$i] / $memCount * 100, 1);
//        if($per < 5) { $vote['cnt'] = "&nbsp;"; }
        echo "
                    <td width={$per}% align=center style=color:".getNewColor($i)."; bgcolor=".getColor($i).">{$totalVote[$i]}</td>
        ";
    }

    echo "
                </tr>
            </table>
        </td>
    </tr>
    ";
}

if($admin['voteopen'] >= 2 || $me['userlevel'] >= 5) {
    $query = "select no from general where nation=0 and npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $memCount = MYDB_num_rows($result);

    if(!$nationVoteCount[0]) { $nationVoteCount[0] = 0; }
    $percentage = @round($nationVoteCount[0] / $memCount * 100, 1);

    echo "
    <tr>
        <td align=center bgcolor=black>재 야</td>
        <td align=center>{$nationVoteCount[0]} / {$memCount} ({$percentage} %)</td>
        <td align=center>
            <table align=center width=100% height=100% border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                <tr>
    ";

    for($i=0; $i < $voteTypeCount; $i++) {
        $per = @round($nationVote[0][$i] / $memCount * 100, 1);
//        if($per < 5) { $vote['cnt'] = "&nbsp;"; }
        echo "
                    <td width={$per}% align=center style=color:".getNewColor($i)."; bgcolor=".getColor($i).">{$nationVote[0][$i]}</td>
        ";
    }

    echo "
                </tr>
            </table>
        </td>
    </tr>
    ";

    $query = "select nation,color,name,gennum from nation order by gennum desc";
    $nationResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationResult);
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationResult);

        $query = "select no from general where nation='{$nation['nation']}' and npc<2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $memCount = MYDB_num_rows($result);

        if(!$nationVoteCount[$nation['nation']]) { $nationVoteCount[$nation['nation']] = 0; }
        $percentage = @round($nationVoteCount[$nation['nation']] / $memCount * 100, 1);

        echo "
    <tr>
        <td align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}>{$nation['name']}</td>
        <td align=center>{$nationVoteCount[$nation['nation']]} / {$memCount} ({$percentage} %)</td>
        <td align=center>
            <table align=center width=100% height=100% border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
                <tr>
        ";

        for($k=0; $k < $voteTypeCount; $k++) {
            $per = @round($nationVote[$nation['nation']][$k] / $memCount * 100, 1);
//            if($per < 5) { $vote['cnt'] = "&nbsp;"; }
            echo "
                    <td width={$per}% align=center style=color:".getNewColor($k)."; bgcolor=".getColor($k).">{$nationVote[$nation['nation']][$k]}</td>
            ";
        }

        echo "
                </tr>
            </table>
        </td>
    </tr>
        ";
    }
}
?>
</form>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php closeButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

<?php
function getColor($type) {
    if($type > 0) {
        $type = (($type - 1) % 7) + 1;
    }

    switch($type) {
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

function getNewColor($type) {
    if($type > 0) {
        $type = (($type - 1) % 7) + 1;
    }

    switch($type) {
    case 2: $color = "black"; break;
    case 3: $color = "black"; break;
    default:$color = "white"; break;
    }

    return $color;
}


