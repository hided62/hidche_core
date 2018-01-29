<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "거래장", 2);

$query = "select no,special,skin,userlevel,con,turntime from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$con = checkLimit($me['userlevel'], $me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

$query = "select no from auction where no1='{$me['no']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$tradeCount = MYDB_num_rows($result);

$query = "select no from auction where no2='{$me['no']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$bidCount = MYDB_num_rows($result);

$btCount = $tradeCount + $bidCount;

if($me['userlevel'] >= 5 || ($me['special'] != 30 && $btCount < 1) || ($me['special'] == 30 && $btCount < 3)) {
    $btn = "submit";
} else {
    $btn = "hidden";
}

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}

if($msg == "") $msg = "-";
if($msg2 == "") $msg2 = "-";
?>
<html>
<head>
<title>거래장</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>거 래 장<br><?php closeButton(); ?></td></tr>
    <tr><td align=center id=bg2><font color=orange size=6><b>거 래 장</b></font><input type=button value='갱신' onclick=location.replace('b_auction.php')></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<form method=post action=c_auction.php>
    <tr><td colspan=11 align=center bgcolor=orange><font size=5>팝 니 다</font></td></tr>
    <tr align=center id=bg1>
        <td width=68>거래번호</td>
        <td width=48>선택</td>
        <td width=98>판매자</td>
        <td width=118>물품</td>
        <td width=88>수량</td>
        <td width=88>시작판매가</td>
        <td width=88>현재판매가</td>
        <td width=88>즉시판매가</td>
        <td width=48>단가</td>
        <td width=98>구매 예정자</td>
        <td width=148>거래종료</td>
    </tr>
<?php
$query = "select * from auction where type=0 order by expire";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$count = MYDB_num_rows($result);

$chk = 0;
for($i=0; $i < $count; $i++) {
    $auction = MYDB_fetch_array($result);
    $itemname = GetStuffName($auction['stuff']);
    $radio = ""; $alert = ""; $alert2 = "";
    if($auction['no1'] == $me['no']) { $radio = " disabled"; }
    elseif($auction['no2'] > 0 && $auction['amount'] * 2 <= $auction['value'] && $auction['stuff'] == 0) { $radio = " disabled"; $alert = "<font color=red>"; $alert2 = "</font>"; }
    elseif($auction['no2'] > 0 && $auction['topv'] <= $auction['value']) { $radio = " disabled"; $alert = "<font color=red>"; $alert2 = "</font>"; }
    elseif($chk == 0) { $radio = " checked"; $chk = 1; }
    $pv = round($auction['value'] * 100 / $auction['amount']) / 100 + 0.001;
    $pv = substr($pv, 0, 4);
    if($auction['stuff'] != 0) { $pv = '-'; }
    echo "
    <tr align=center>
        <td>{$auction['no']}</td>
        <td><input type=radio name=sel value={$auction['no']}{$radio}></td>
        <td>{$auction['name1']}</td>
        <td>{$itemname}</td>
        <td>{$auction['amount']}</td>
        <td>금 {$auction['cost']}</td>
        <td>{$alert}금 {$auction['value']}{$alert2}</td>
        <td>{$alert}금 {$auction['topv']}{$alert2}</td>
        <td>{$alert}{$pv}{$alert2}</td>
        <td>{$alert}{$auction['name2']}{$alert2}</td>
        <td>{$auction['expire']}</td>
    </tr>
    ";
}
?>
    <tr height=25>
        <td align=center id=bg1>등록결과</td>
        <td colspan=10><?=ConvertLog($msg);?></td>
    </tr>
    <tr>
        <td align=center id=bg1>입찰등록</td>
        <td colspan=10>
            　지불할 금액: <input type=text style=color:white;background-color:black; size=6 maxlength=6 name=value>
            <input type=<?=$btn;?> name=btn value='구매시도' onclick='return confirm("정말 입찰하시겠습니까?");'>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1>거래등록</td>
        <td colspan=10>
            　종료: <input type=text style=color:white;background-color:black; size=2 maxlength=2 name=term value=12>턴 후
            　물품: <select size=1 name=stuff style=color:white;background-color:black;>
                <option style=color:white; value=0>쌀</option>
            </select>
            　판매량: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=amount value=1000>
            　시작가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=cost value=500>
            　즉구가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=topv value=2000>
            <input type=<?=$btn;?> name=btn value='판매' onclick='return confirm("정말 판매하시겠습니까?");'>
        </td>
    </tr>
    <tr>
        <td colspan=11>
            ㆍ<font color=cyan>Hint</font>) 거래자가 판매(물품 판매, 금 수령), 입찰자가 구매(물품 구입, 금 지불).<br>
            ㆍ<font color=cyan>Hint</font>) 단가가 1.00보다 높을수록 판매자 유리.<br>
            ㆍ<font color=cyan>Hint</font>) 단가가 1.00보다 낮을수록 입찰자 유리.<br>
        </td>
    </tr>
</form>
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<form method=post action=c_auction.php>
    <tr><td colspan=11 align=center bgcolor=skyblue><font size=5>삽 니 다</font></td></tr>
    <tr align=center id=bg1>
        <td width=68>거래번호</td>
        <td width=48>선택</td>
        <td width=98>구매자</td>
        <td width=118>물품</td>
        <td width=88>수량</td>
        <td width=88>시작구매가</td>
        <td width=88>현재구매가</td>
        <td width=88>즉시구매가</td>
        <td width=48>단가</td>
        <td width=98>판매 예정자</td>
        <td width=148>거래종료</td>
    </tr>
<?php
$query = "select * from auction where type=1 order by expire";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$count = MYDB_num_rows($result);

$chk = 0;
for($i=0; $i < $count; $i++) {
    $auction = MYDB_fetch_array($result);
    $itemname = GetStuffName($auction['stuff']);
    $radio = ""; $alert = ""; $alert2 = "";
    if($auction['no1'] == $me['no']) { $radio = " disabled"; }
    elseif($auction['no2'] > 0 && $auction['amount'] >= $auction['value'] * 2 && $auction['stuff'] == 0) { $radio = " disabled"; $alert = "<font color=red>"; $alert2 = "</font>"; }
    elseif($auction['no2'] > 0 && $auction['topv'] >= $auction['value']) { $radio = " disabled"; $alert = "<font color=red>"; $alert2 = "</font>"; }
    elseif($chk == 0) { $radio = " checked"; $chk = 1; }
    $pv = round($auction['value'] * 100 / $auction['amount']) / 100 + 0.001;
    $pv = substr($pv, 0, 4);
    if($auction['stuff'] != 0) { $pv = '-'; }
    echo "
    <tr align=center>
        <td>{$auction['no']}</td>
        <td><input type=radio name=sel value={$auction['no']}{$radio}></td>
        <td>{$auction['name1']}</td>
        <td>{$itemname}</td>
        <td>{$auction['amount']}</td>
        <td>금 {$auction['cost']}</td>
        <td>{$alert}금 {$auction['value']}{$alert2}</td>
        <td>{$alert}금 {$auction['topv']}{$alert2}</td>
        <td>{$alert}{$pv}{$alert2}</td>
        <td>{$alert}{$auction['name2']}{$alert2}</td>
        <td>{$auction['expire']}</td>
    </tr>
    ";
}
?>
    <tr height=25>
        <td align=center id=bg1>등록결과</td>
        <td colspan=10><?=ConvertLog($msg2);?></td>
    </tr>
    <tr>
        <td align=center id=bg1>입찰등록</td>
        <td colspan=10>
            　수령할 금액: <input type=text style=color:white;background-color:black; size=6 maxlength=6 name=value>
            <input type=<?=$btn;?> name=btn value='판매시도' onclick='return confirm("정말 입찰하시겠습니까?");'>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1>거래등록</td>
        <td colspan=10>
            　종료: <input type=text style=color:white;background-color:black; size=2 maxlength=2 name=term value=12>턴 후
            　물품: <select size=1 name=stuff style=color:white;background-color:black;>
                <option style=color:white; value=0>쌀</option>
            </select>
            　구입량: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=amount value=1000>
            　시작가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=cost value=2000>
            　즉구가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=topv value=500>
            <input type=<?=$btn;?> name=btn value='구매' onclick='return confirm("정말 구매하시겠습니까?");'>
        </td>
    </tr>
    <tr>
        <td colspan=11>
            ㆍ<font color=cyan>Hint</font>) 거래자가 구매(물품 구매, 금 지불), 입찰자가 판매(물품 판매, 금 수령).<br>
            ㆍ<font color=cyan>Hint</font>) 단가가 1.00보다 낮을수록 구매자 유리.<br>
            ㆍ<font color=cyan>Hint</font>) 단가가 1.00보다 높을수록 입찰자 유리.<br>
        </td>
    </tr>
</form>
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td align=center id=bg2><font size=5>최 근 기 록</font></td></tr>
    <tr><td>
    <?=AuctionLog(20, $me['skin']);?>
    </td></tr>
    <tr><td align=center id=bg2><font size=5>도 움 말</font></td></tr>
    <tr><td>
        <font color=white size=2>
ㆍ판매거래는 거래자가 판매할 물품을 거래하면, 구입을 희망하는 사람이 현재가보다 높게 입찰하여 구입하는 방식입니다.<br>
ㆍ<font color=cyan>Hint</font>) 쌀이 귀한 경우는 입찰자가 많아서 자연스레 단가가 오르게 됩니다. (해당 물품을 사려는 가격이 오름)<br>
ㆍ<font color=cyan>Hint</font>) 쌀이 흔한 경우는 초기 가격을 낮게 책정해야 판매가 가능할 겁니다.<br>
ㆍ구매거래는 거래자가 구입할 물품을 거래하면, 판매를 희망하는 사람이 현재가보다 낮게 입찰하여 판매하는 방식입니다.<br>
ㆍ<font color=cyan>Hint</font>) 쌀이 흔한 경우는 입찰자가 많아서 자연스레 단가가 내리게 됩니다. (해당 물품을 팔려는 가격이 내림)<br>
ㆍ<font color=cyan>Hint</font>) 쌀이 귀한 경우는 초기 가격을 높게 책정해야 구입이 가능할 겁니다.<br>
ㆍ마감임박때 입찰하는 경우 입찰후 1턴 후로 종료시간이 연장됩니다.<br>
ㆍ즉시구매가로 입찰하는 경우 입찰후 1턴 후로 종료시간이 결정됩니다.<br>
ㆍ악용 방지를 위해 50% ~ 200%의 가격에서 거래시작이 가능합니다.<br>
ㆍ악용 방지를 위해 즉시판매가는 110% 이상, 즉시구매가는 90% 이하의 시세로 가능합니다.<br>
ㆍ악용 방지를 위해 즉시판매가는 시작판매가의 110% 이상, 즉시구매가는 시작구매가의 90% 이하로 가능합니다.<br>
ㆍ1인당 도합 1건의 거래와 입찰이 가능합니다. 거상 특기 소유자는 1인당 도합 3건입니다.<br>
ㆍ기본금쌀 1000은 거래에 사용되지 못합니다.<br>
ㆍ유찰될 때는 거래 과실자에게 거래금의 1%가 벌금으로 부과됩니다.<br>
ㆍ<font color=magenta>10단위로 거래가 가능합니다. 1자리는 반올림 처리 됩니다.</font><br>
ㆍ<font color=red>★ 최고가 거래 ★</font> 혹은 <font color=red>★ 최저가 거래 ★</font> 는 암거래 및 악용의 가능성이니 감시 부탁드립니다.<br>
ㆍ거래와 입찰은 취소가 불가능하니 주의하세요!<br>
ㆍ<font color=cyan>Hint</font>) 단가는 금/쌀로 쌀1을 거래하기 위한 금의 양입니다.<br>
ㆍ<font color=cyan>Hint</font>) 단가가 높으면(>1.00) 쌀이 비싸므로 판매가 이득입니다.<br>
ㆍ<font color=cyan>Hint</font>) 단가가 낮으면(<1.00) 금이 비싸므로 구매가 이득입니다.<br>
ㆍ즐거운 거래!
        </font>
    </td></tr>
    <tr><td><?php closeButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
