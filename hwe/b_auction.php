<?php
namespace sammo;

include "lib.php";
include "func.php";

$msg = Util::getReq('msg');
$msg2 = Util::getReq('msg2');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("거래장", 2);

$me = $db->queryFirstRow('SELECT no,special,con,turntime from general where owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$tradeCount = $db->queryFirstField('SELECT count(no) FROM auction WHERE no1=%i', $me['no']);
$bidCount = $db->queryFirstField('SELECT count(no) FROM auction where no2=%i', $me['no']);

$btCount = $tradeCount + $bidCount;

if ($session->userGrade >= 5 || $btCount < 1) {
    $btn = "submit";
} else {
    $btn = "hidden";
}

if ($msg == "") {
    $msg = "-";
}
if ($msg2 == "") {
    $msg2 = "-";
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 거래장</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>거 래 장<br><?=closeButton()?></td></tr>
    <tr><td align=center id=bg2><font color=orange size=6><b>거 래 장</b></font><input type=button value='갱신' onclick="location.replace('b_auction.php')"></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
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
$chk = 0;
foreach($db->query('SELECT * from auction where type=0 order by expire') as $auction){
    $radio = "";
    $alert = "";
    $alert2 = "";
    if ($auction['no1'] == $me['no']) {
        $radio = " disabled";
    } elseif ($auction['no2'] > 0 && $auction['amount'] * 2 <= $auction['value']) {
        $radio = " disabled";
        $alert = "<font color=red>";
        $alert2 = "</font>";
    } elseif ($auction['no2'] > 0 && $auction['topv'] <= $auction['value']) {
        $radio = " disabled";
        $alert = "<font color=red>";
        $alert2 = "</font>";
    } elseif ($chk == 0) {
        $radio = " checked";
        $chk = 1;
    }
    $pv = round($auction['value'] * 100 / $auction['amount']) / 100 + 0.001;
    $pv = substr((string)$pv, 0, 4);

    echo "
    <tr align=center>
        <td>{$auction['no']}</td>
        <td><input type=radio name=sel value={$auction['no']}{$radio}></td>
        <td>{$auction['name1']}</td>
        <td>쌀</td>
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
        <td colspan=10><?=ConvertLog($msg)?></td>
    </tr>
    <tr>
        <td align=center id=bg1>입찰등록</td>
        <td colspan=10>
            　지불할 금액: <input type=text style=color:white;background-color:black; size=6 maxlength=6 name=value>
            <input type=<?=$btn?> name=btn value='구매시도' onclick='return confirm("정말 입찰하시겠습니까?");'>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1>거래등록</td>
        <td colspan=10>
            　종료: <input type=text style=color:white;background-color:black; size=2 maxlength=2 name=term value=12>턴 후
            　물품: 쌀
            　판매량: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=amount value=1000>
            　시작가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=cost value=500>
            　즉구가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=topv value=2000>
            <input type=<?=$btn?> name=btn value='판매' onclick='return confirm("정말 판매하시겠습니까?");'>
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
<table align=center width=1000 class='tb_layout bg0'>
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
$chk = 0;
foreach($db->query('SELECT * from auction where type=1 order by expire') as $auction){
    $radio = "";
    $alert = "";
    $alert2 = "";
    if ($auction['no1'] == $me['no']) {
        $radio = " disabled";
    } elseif ($auction['no2'] > 0 && $auction['amount'] >= $auction['value'] * 2) {
        $radio = " disabled";
        $alert = "<font color=red>";
        $alert2 = "</font>";
    } elseif ($auction['no2'] > 0 && $auction['topv'] >= $auction['value']) {
        $radio = " disabled";
        $alert = "<font color=red>";
        $alert2 = "</font>";
    } elseif ($chk == 0) {
        $radio = " checked";
        $chk = 1;
    }
    $pv = round($auction['value'] * 100 / $auction['amount']) / 100 + 0.001;
    $pv = substr((string)$pv, 0, 4);
    echo "
    <tr align=center>
        <td>{$auction['no']}</td>
        <td><input type=radio name=sel value={$auction['no']}{$radio}></td>
        <td>{$auction['name1']}</td>
        <td>쌀</td>
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
        <td colspan=10><?=ConvertLog($msg2)?></td>
    </tr>
    <tr>
        <td align=center id=bg1>입찰등록</td>
        <td colspan=10>
            　수령할 금액: <input type=text style=color:white;background-color:black; size=6 maxlength=6 name=value>
            <input type=<?=$btn?> name=btn value='판매시도' onclick='return confirm("정말 입찰하시겠습니까?");'>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1>거래등록</td>
        <td colspan=10>
            　종료: <input type=text style=color:white;background-color:black; size=2 maxlength=2 name=term value=12>턴 후
            　물품: 쌀
            　구입량: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=amount value=1000>
            　시작가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=cost value=2000>
            　즉구가: <input type=text style=color:white;background-color:black; size=5 maxlength=5 name=topv value=500>
            <input type=<?=$btn?> name=btn value='구매' onclick='return confirm("정말 구매하시겠습니까?");'>
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
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td align=center id=bg2><font size=5>최 근 기 록</font></td></tr>
    <tr><td>
    <?=getAuctionLogRecent(20)?>
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
ㆍ1인당 도합 1건의 거래와 입찰이 가능합니다.<br>
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
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
