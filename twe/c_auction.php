<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "입찰", 1);

$query = "select turnterm from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,gold,rice,special,userlevel from general where no_member='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select no from auction where no1='{$me['no']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$tradeCount = MYDB_num_rows($result);

$query = "select no from auction where no2='{$me['no']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$bidCount = MYDB_num_rows($result);

$btCount = $tradeCount + $bidCount;

switch($admin['turnterm']) {
case 0: $unit = 7200; break;
case 1: $unit = 3600; break;
case 2: $unit = 1800; break;
case 3: $unit = 1200; break;
case 4: $unit = 600; break;
case 5: $unit = 300; break;
case 6: $unit = 120; break;
case 7: $unit = 60; break;
}

$amount = round($amount / 10) * 10;
$cost = round($cost / 10) * 10;
$topv = round($topv / 10) * 10;
$value = round($value / 10) * 10;
if($term > 24) $term = 24;

$valid = 1;
if($me['userlevel'] >= 5 || ($me['special'] != 30 && $btCount < 1) || ($me['special'] == 30 && $btCount < 3)) {
} else {
    $msg = "ㆍ<O>더이상 등록할 수 없습니다.</>";
    $msg2 = "ㆍ<O>더이상 등록할 수 없습니다.</>";
    $valid = 0;
    $btn = "hidden";
}

if($btn == "판매") {
    if($stuff != 0) {
        $msg = "ㆍ<O>현재 쌀만 거래 가능합니다.</>";
        $valid = 0;
    }
    if($term < 0 || $term > 24) {
        $msg = "ㆍ<O>종료기한은 1 ~ 24 턴 이어야 합니다.</>";
        $valid = 0;
    }
    if($amount < 100 || $amount > 10000) {
        $msg = "ㆍ<O>거래량은 100 ~ 10000 이어야 합니다.</>";
        $valid = 0;
    }
    if($cost > $amount * 2 || $cost * 2 < $amount) {
        $msg = "ㆍ<O>시작판매가는 50% ~ 200% 이어야 합니다.</>";
        $valid = 0;
    }
    if($topv < $amount*1.1 || $topv > $amount * 2) {
        $msg = "ㆍ<O>즉시판매가는 110% ~ 200% 이어야 합니다.</>";
        $valid = 0;
    }
    if($topv < $cost*1.1) {
        $msg = "ㆍ<O>즉시판매가는 시작판매가의 110% 이상이어야 합니다.</>";
        $valid = 0;
    }
    if($amount > $me['rice'] - 1000) {
        $msg = "ㆍ<O>기본 군량 1000은 거래할 수 없습니다.</>";
        $valid = 0;
    }
    if($valid == 1) {
        $msg = "ㆍ<O>등록 성공.</>";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (0, '{$me['no']}', '{$me['name']}', '$stuff', '$amount', '$cost', '$cost', '$topv', '$date')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "구매시도") {
    $query = "select no2,value,topv,expire,amount from auction where no='$sel'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $auction = MYDB_fetch_array($result);

    if($value == $auction['topv']) {
        $valid = 2;
    }
    if(!$auction) {
        $msg = "ㆍ<O>종료된 거래입니다.</>";
        $valid = 0;
    }
/*
    if($stuff != 0) {
        $msg = "ㆍ<O>현재 쌀만 거래 가능합니다.</>";
        $valid = 0;
    }
*/
    if($auction['no2'] > 0 && $value <= $auction['value']) {
        $msg = "ㆍ<O>현재판매가보다 높게 입찰해야 합니다.</>";
        $valid = 0;
    }
    if($value < $auction['value']) {
        $msg = "ㆍ<O>현재판매가보다 높게 입찰해야 합니다.</>";
        $valid = 0;
    }
    if($value > $auction['topv']) {
        $msg = "ㆍ<O>즉시판매가보다 높을 수 없습니다.</>";
        $valid = 0;
    }
    if($value > $me['gold'] - 1000) {
        $msg = "ㆍ<O>기본 자금 1000은 거래할 수 없습니다.</>";
        $valid = 0;
    }
    if($valid == 1) {
        $msg = "ㆍ<O>입찰 성공.</> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);
        if($auction['expire'] > $date) $date = $auction['expire'];
        $query = "update auction set value='$value',no2='{$me['no']}',name2='{$me['name']}',expire='$date' where no='$sel'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($valid == 2) {
        $msg = "ㆍ<O>즉시판매 성공.</> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);
        $query = "update auction set value='$value',no2='{$me['no']}',name2='{$me['name']}',expire='$date' where no='$sel'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "구매") {
    if($stuff != 0) {
        $msg2 = "ㆍ<O>현재 쌀만 거래 가능합니다.</>";
        $valid = 0;
    }
    if($term < 0 || $term > 24) {
        $msg2 = "ㆍ<O>종료기한은 1 ~ 24 턴 이어야 합니다.</>";
        $valid = 0;
    }
    if($amount < 100 || $amount > 10000) {
        $msg2 = "ㆍ<O>거래량은 100 ~ 10000 이어야 합니다.</>";
        $valid = 0;
    }
    if($cost > $amount * 2 || $cost * 2 < $amount) {
        $msg2 = "ㆍ<O>시작구매가는 50% ~ 200% 이어야 합니다.</>";
        $valid = 0;
    }
    if($topv < $amount * 0.5 || $topv > $amount * 0.9) {
        $msg2 = "ㆍ<O>즉시구매가는 50% ~ 90% 이어야 합니다.</>";
        $valid = 0;
    }
    if($topv > $cost * 0.9) {
        $msg2 = "ㆍ<O>즉시구매가는 시작구매가의 90% 이하이어야 합니다.</>";
        $valid = 0;
    }
    if($cost > $me['gold'] - 1000) {
        $msg2 = "ㆍ<O>기본 자금 1000은 거래할 수 없습니다.</>";
        $valid = 0;
    }
    if($valid == 1) {
        $msg2 = "ㆍ<O>등록 성공.</>";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (1, '{$me['no']}', '{$me['name']}', '$stuff', '$amount', '$cost', '$cost', '$topv', '$date')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "판매시도") {
    $query = "select no2,value,topv,expire,amount from auction where no='$sel'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $auction = MYDB_fetch_array($result);

    if($value == $auction['topv']) {
        $valid = 2;
    }
    if(!$auction) {
        $msg2 = "ㆍ<O>종료된 거래입니다.</>";
        $valid = 0;
    }
    if($stuff != 0) {
        $msg2 = "ㆍ<O>현재 쌀만 거래 가능합니다.</>";
        $valid = 0;
    }
    if($auction['no2'] > 0 && $value >= $auction['value']) {
        $msg2 = "ㆍ<O>현재구매가보다 낮게 입찰해야 합니다.</>";
        $valid = 0;
    }
    if($value > $auction['value']) {
        $msg2 = "ㆍ<O>현재구매가보다 낮게 입찰해야 합니다.</>";
        $valid = 0;
    }
    if($value < $auction['topv']) {
        $msg2 = "ㆍ<O>즉시구매가보다 낮을 수 없습니다.</>";
        $valid = 0;
    }
    if($value > $me['rice'] - 1000) {
        $msg2 = "ㆍ<O>기본 군량 1000은 거래할 수 없습니다.</>";
        $valid = 0;
    }
    if($valid == 1) {
        $msg2 = "ㆍ<O>입찰 성공.</> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);
        if($auction['expire'] > $date) $date = $auction['expire'];
        $query = "update auction set value='$value',no2='{$me['no']}',name2='{$me['name']}',expire='$date' where no='$sel'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($valid == 2) {
        $msg2 = "ㆍ<O>즉시구매 성공.</> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);
        $query = "update auction set value='$value',no2='{$me['no']}',name2='{$me['name']}',expire='$date' where no='$sel'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

Submit("b_auction.php", $msg, $msg2);

