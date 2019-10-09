<?php
namespace sammo;

include "lib.php";
include "func.php";

$v = new Validator($_POST + $_GET);
$v->rule('integer', [
    'amount',
    'cost',
    'topv',
    'value',
    'term',
    'sel'
]);

$btn = Util::getReq('btn');
$amount = Util::getReq('amount', 'int');
$cost = Util::getReq('cost', 'int');
$topv = Util::getReq('topv', 'int');
$value = Util::getReq('value', 'int');
$term = Util::getReq('term', 'int');
$sel = Util::getReq('sel', 'int');

$msg = '';
$msg2 = '';


//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("입찰", 1);

$turnterm = $gameStor->turnterm;

$me = $db->queryFirstRow('SELECT no,name,gold,rice,special from general where owner=%i', $userID);

$tradeCount = $db->queryFirstField('SELECT count(no) FROM auction WHERE no1=%i', $me['no']);
$bidCount = $db->queryFirstField('SELECT count(no) FROM auction WHERE no2=%i', $me['no']);

$btCount = $tradeCount + $bidCount;

$unit = $turnterm * 60;

$amount = Util::round($amount, -1);
$cost = Util::round($cost, -1);
$topv = Util::round($topv, -1);
$value = Util::round($value, -1);
if ($term > 24) {
    $term = 24;
}

$valid = 1;
if ($session->userGrade >= 5) {
} else {
    $msg = "ㆍ<span class='ev_warning'>더이상 등록할 수 없습니다.</span>";
    $msg2 = "ㆍ<span class='ev_warning'>더이상 등록할 수 없습니다.</span>";
    $valid = 0;
    $btn = "hidden";
}

if ($btn == "판매") {
    if ($term < 0 || $term > 24) {
        $msg = "ㆍ<span class='ev_warning'>종료기한은 1 ~ 24 턴 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($amount < 100 || $amount > 10000) {
        $msg = "ㆍ<span class='ev_warning'>거래량은 100 ~ 10000 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($cost > $amount * 2 || $cost * 2 < $amount) {
        $msg = "ㆍ<span class='ev_warning'>시작판매가는 50% ~ 200% 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($topv * 10 < $amount * 11 || $topv > $amount * 2) {
        $msg = "ㆍ<span class='ev_warning'>즉시판매가는 110% ~ 200% 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($topv * 10 < $cost * 11) {
        $msg = "ㆍ<span class='ev_warning'>즉시판매가는 시작판매가의 110% 이상이어야 합니다.</span>";
        $valid = 0;
    }
    if ($amount > $me['rice'] - GameConst::$defaultRice) {
        $msg = "ㆍ<span class='ev_warning'>기본 군량 ".GameConst::$defaultRice."은 거래할 수 없습니다.</span>";
        $valid = 0;
    }
    if ($valid == 1) {
        $msg = "ㆍ<span class='ev_success'>등록 성공.</span>";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $db->insert('auction', [
            'type'=>0,
            'no1'=>$me['no'],
            'name1'=>$me['name'],
            'amount'=>$amount,
            'cost'=>$cost,
            'value'=>$cost,
            'topv'=>$topv,
            'expire'=>$date
        ]);
    }
} elseif ($btn == "구매시도") {
    $auction = $db->queryFirstRow('SELECT no2,value,topv,expire,amount FROM auction WHERE no=%i LIMIT 1', $sel);

    if ($value == $auction['topv']) {
        $valid = 2;
    }
    if (!$auction) {
        $msg = "ㆍ<span class='ev_warning'>종료된 거래입니다.</span>";
        $valid = 0;
    }
    if ($auction['no2'] > 0 && $value <= $auction['value']) {
        $msg = "ㆍ<span class='ev_warning'>현재판매가보다 높게 입찰해야 합니다.</span>";
        $valid = 0;
    }
    if ($value < $auction['value']) {
        $msg = "ㆍ<span class='ev_warning'>현재판매가보다 높게 입찰해야 합니다.</span>";
        $valid = 0;
    }
    if ($value > $auction['topv']) {
        $msg = "ㆍ<span class='ev_warning'>즉시판매가보다 높을 수 없습니다.</span>";
        $valid = 0;
    }
    if ($value > $me['gold'] - GameConst::$defaultGold) {
        $msg = "ㆍ<span class='ev_warning'>기본 자금 ".GameConst::$defaultGold."은 거래할 수 없습니다.</span>";
        $valid = 0;
    }
    if ($valid == 1) {
        $msg = "ㆍ<span class='ev_success'>입찰 성공.</span> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);
        if ($auction['expire'] > $date) {
            $date = $auction['expire'];
        }
        
        $db->update('auction', [
            'value'=>$value,
            'no2'=>$me['no'],
            'name2'=>$me['name'],
            'expire'=>$date,
        ], 'no=%i', $sel);
    } elseif ($valid == 2) {
        $msg = "ㆍ<span class='ev_success'>즉시판매 성공.</span> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);

        $db->update('auction', [
            'value'=>$value,
            'no2'=>$me['no'],
            'name2'=>$me['name'],
            'expire'=>$date,
        ], 'no=%i', $sel);
    }
} elseif ($btn == "구매") {
    if ($term < 0 || $term > 24) {
        $msg2 = "ㆍ<span class='ev_warning'>종료기한은 1 ~ 24 턴 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($amount < 100 || $amount > 10000) {
        $msg2 = "ㆍ<span class='ev_warning'>거래량은 100 ~ 10000 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($cost > $amount * 2 || $cost * 2 < $amount) {
        $msg2 = "ㆍ<span class='ev_warning'>시작구매가는 50% ~ 200% 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($topv < $amount * 0.5 || $topv > $amount * 0.9) {
        $msg2 = "ㆍ<span class='ev_warning'>즉시구매가는 50% ~ 90% 이어야 합니다.</span>";
        $valid = 0;
    }
    if ($topv > $cost * 0.9) {
        $msg2 = "ㆍ<span class='ev_warning'>즉시구매가는 시작구매가의 90% 이하이어야 합니다.</span>";
        $valid = 0;
    }
    if ($cost > $me['gold'] - GameConst::$defaultGold) {
        $msg2 = "ㆍ<span class='ev_warning'>기본 자금 ".GameConst::$defaultGold."은 거래할 수 없습니다.</span>";
        $valid = 0;
    }
    if ($valid == 1) {
        $msg2 = "ㆍ<span class='ev_success'>등록 성공.</span>";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);

        $db->insert('auction', [
            'type'=>1,
            'no1'=>$me['no'],
            'name1'=>$me['name'],
            'amount'=>$amount, 
            'cost'=>$cost, 
            'value'=>$cost, 
            'topv'=>$topv, 
            'expire'=>$date
        ]);
    }
} elseif ($btn == "판매시도") {
    $auction = $db->queryFirstRow('SELECT no2,value,topv,expire,amount FROM auction WHERE no=%i LIMIT 1', $sel);

    if ($value == $auction['topv']) {
        $valid = 2;
    }
    if (!$auction) {
        $msg2 = "ㆍ<span class='ev_warning'>종료된 거래입니다.</span>";
        $valid = 0;
    }
    if ($auction['no2'] > 0 && $value >= $auction['value']) {
        $msg2 = "ㆍ<span class='ev_warning'>현재구매가보다 낮게 입찰해야 합니다.</span>";
        $valid = 0;
    }
    if ($value > $auction['value']) {
        $msg2 = "ㆍ<span class='ev_warning'>현재구매가보다 낮게 입찰해야 합니다.</span>";
        $valid = 0;
    }
    if ($value < $auction['topv']) {
        $msg2 = "ㆍ<span class='ev_warning'>즉시구매가보다 낮을 수 없습니다.</span>";
        $valid = 0;
    }
    if ($value > $me['rice'] - GameConst::$defaultRice) {
        $msg2 = "ㆍ<span class='ev_warning'>기본 군량 ".GameConst::$defaultRice."은 거래할 수 없습니다.</span>";
        $valid = 0;
    }
    if ($valid == 1) {
        $msg2 = "ㆍ<span class='ev_success'>입찰 성공.</span> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);
        if ($auction['expire'] > $date) {
            $date = $auction['expire'];
        }

        $db->update('auction', [
            'value'=>$value,
            'no2'=>$me['no'],
            'name2'=>$me['name'],
            'expire'=>$date,
        ], 'no=%i', $sel);
    } elseif ($valid == 2) {
        $msg2 = "ㆍ<span class='ev_success'>즉시구매 성공.</span> 거래완료는 빨라도 현재로부터 1턴 뒤입니다.";
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit);

        $db->update('auction', [
            'value'=>$value,
            'no2'=>$me['no'],
            'name2'=>$me['name'],
            'expire'=>$date,
        ], 'no=%i', $sel);
    }
}

Submit("b_auction.php", $msg, $msg2);
